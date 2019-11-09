<?php

namespace Diaz\Shared\Infrastructure\RabbitMQ;

use Closure;
use Diaz\Shared\Domain\Logger\Logger;
use ErrorException;
use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class RabbitMQConsumer
 * @package Diaz\Shared\Infrastructure\RabbitMQ
 */
class RabbitMQConsumer
{
    const MAX_RETRIES = 5;
    const INITIAL_RETRIES = 0;
    const RETRIES = 'x-delivered-count';

    /** @var AMQPStreamConnection */
    private $connection;
    /** @var string */
    private $exchange;
    /** @var AMQPChannel */
    private $channel;
    /** @var Logger */
    private $logger;
    /** @var string */
    private $queue;

    /**
     * RabbitMQConsumer constructor.
     * @param RabbitMQConnection $connection
     * @param string $exchange
     * @param Logger $logger
     */
    public function __construct(RabbitMQConnection $connection, $exchange, Logger $logger)
    {
        $this->connection = $this->getConnection($connection);
        $this->exchange = $exchange;
        $this->logger = $logger;
    }

    /**
     * @param RabbitMQConnection $connection
     * @return AMQPStreamConnection
     * @throws InvalidArgumentException
     */
    private function getConnection(RabbitMQConnection $connection)
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->connection = new AMQPStreamConnection(
            $connection->getHost(),
            $connection->getPort(),
            $connection->getUser(),
            $connection->getPass(),
            $connection->getVHosts()
        );

        return $this->connection;
    }

    /**
     * @param string $queueName
     * @param string $routingKey
     * @param Closure $callback
     * @throws ErrorException
     */
    public function __invoke($queueName, $routingKey, Closure $callback)
    {
        $channel = $this->getChannel($queueName, $callback);
        $channel->wait(null, true);
    }

    /**
     * @param string $queueName
     * @param Closure $callback
     * @return AMQPChannel
     */
    private function getChannel($queueName, Closure $callback)
    {
        if ($this->channel === null) {
            $this->queue = $queueName;
            $consumerTag = 'consumer_' . date('u');
            $this->channel = $this->connection->channel();
            $this->channel->basic_consume($this->queue, $consumerTag, false, false,
                false, false, $this->consume($callback));

            return $this->channel;
        }

        return $this->channel;
    }

    /**
     * @param Closure $callback
     * @return Closure
     */
    private function consume(Closure $callback)
    {
        return function (AMQPMessage $message) use ($callback) {
            try {
                $message->get('channel')->basic_ack($message->get('delivery_tag'));
                $callback($message->getBody());
                $this->log($message, 'info');
            } catch (Exception $e) {
                $this->log($message, 'error');
                if (!$message->has('application_headers')) {
                    $table = new AMQPTable([self::RETRIES => self::INITIAL_RETRIES]);
                    $message->set('application_headers', $table);
                }
                /** @var AMQPTable $properties */
                $properties = $message->get('application_headers');
                $dataAsArray = $properties->getNativeData();
                if ($dataAsArray[self::RETRIES] < RabbitMQConsumer::MAX_RETRIES) {
                    $increasedRetries = $dataAsArray[self::RETRIES] + 1;
                    $properties->set(self::RETRIES, $increasedRetries);
                    $message->get('channel')->basic_publish($message, 'retry-' . $this->exchange, $this->queue);
                }
                if ($dataAsArray[self::RETRIES] >= RabbitMQConsumer::MAX_RETRIES) {
                    $increasedRetries = $dataAsArray[self::RETRIES] + 1;
                    $properties->set(self::RETRIES, $increasedRetries);
                    $message->get('channel')->basic_publish($message, 'dead_letter-' . $this->exchange, $this->queue);
                }
            }
        };
    }

    /**
     * @param AMQPMessage $message
     * @param string $levelLog
     */
    private function log(AMQPMessage $message, $levelLog)
    {
        $messageData = json_decode($message->getBody(), true);
        $this->logger->log($levelLog, 'Message Consumed', [
            'message_id' => $messageData['message_id'],
            'occurred_on' => $messageData['occurred_on'],
            'exchange' => $message->delivery_info['exchange'],
            'routing_key' => $message->delivery_info['routing_key'],
            'queue' => $this->queue,
            'body' => $messageData
        ]);
    }

    /**
     * @throws Exception
     */
    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

}
