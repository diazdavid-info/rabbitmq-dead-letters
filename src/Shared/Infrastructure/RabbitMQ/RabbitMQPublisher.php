<?php

namespace Diaz\Shared\Infrastructure\RabbitMQ;

use Diaz\Shared\Domain\Logger\Logger;
use Diaz\Shared\Domain\Publisher\Message;
use Diaz\Shared\Domain\Publisher\Publisher;
use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQPublisher
 * @package Diaz\Shared\Infrastructure\RabbitMQ
 */
class RabbitMQPublisher implements Publisher
{
    const INITIAL_RETRIES = 0;
    const RETRIES = 'x-delivered-count';
    const RABBIT_INT_TYPE = 's';

    /** @var AMQPStreamConnection */
    private $connection;
    /** @var string */
    private $exchange;
    /** @var RabbitMQConnection */
    private $configConnection;
    /** @var Logger */
    private $logger;

    /**
     * RabbitMQConsumer constructor.
     * @param RabbitMQConnection $configConnection
     * @param string $exchange
     * @param Logger $logger
     */
    public function __construct(RabbitMQConnection $configConnection, $exchange, Logger $logger)
    {
        $this->configConnection = $configConnection;
        $this->exchange = $exchange;
        $this->logger = $logger;
    }

    /**
     * @param Message $message
     * @throws Exception
     */
    public function publish(Message $message)
    {
        $conection = $this->getConnection();
        $exchange = $this->exchange;
        $routingKey = $message->type();

        $channel = $conection->channel();
        $channel->exchange_declare($exchange, AMQPExchangeType::TOPIC, false, true);
        $amqpMessage = new AMQPMessage(
            json_encode($message->toArray()),
            [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'application_headers' => [self::RETRIES => [self::RABBIT_INT_TYPE, self::INITIAL_RETRIES]]
            ]
        );
        $channel->basic_publish($amqpMessage, $exchange, $routingKey);

        $channel->close();
        $conection->close();
        $this->logger->info('Message Published', [
            'message_id' => $message->getMessageId(),
            'occurred_on' => $message->getOccurredOn(),
            'exchange' => $this->exchange,
            'routing_key' => $message->type(),
            'queue' => '',
            'body' => $message->toArray()
        ]);
    }

    /**
     * @return AMQPStreamConnection
     * @throws InvalidArgumentException
     */
    private function getConnection()
    {
        if ($this->connection !== null && $this->connection->isConnected()) {
            return $this->connection;
        }

        $this->connection = new AMQPStreamConnection(
            $this->configConnection->getHost(),
            $this->configConnection->getPort(),
            $this->configConnection->getUser(),
            $this->configConnection->getPass(),
            $this->configConnection->getVHosts()
        );

        return $this->connection;
    }
}
