<?php

namespace Diaz\TestingBackend\command\configuration;

use Diaz\Shared\Infrastructure\RabbitMQ\RabbitMQConnection;
use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Lambdish\Phunctional\each;

/**
 * Class RabbitConfigurationCommand
 * @package Diaz\TestingBackend\command\configuration
 */
class RabbitConfigurationCommand extends Command
{
    /** @var AMQPStreamConnection */
    private $connection;
    /** @var AMQPChannel */
    private $channel;

    /**
     * ConsumeRandomMessageCommand constructor.
     * @param RabbitMQConnection $connection
     */
    public function __construct(RabbitMQConnection $connection)
    {
        parent::__construct(null);
        $this->connection = $this->getConnection($connection);
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
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('testing:configuration:rabbit_configuration')
            ->setDescription('Configurar toda la infra de rabbit');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configList = [
            [
                'exchange' => 'domain_event',
                'queue' => 'testing.random_checked',
                'routing_key' => 'diaz.random.event.random.checked'
            ]
        ];

        $this->channel = $this->connection->channel();

        each(function ($config) {
            $this->declareExchanges($config['exchange']);
            $this->declareQueues($config['queue'], $config['exchange']);
            $this->declareBinds($config['queue'], $config['exchange'], $config['routing_key']);
        }, $configList);

    }

    /**
     * @param string $exchange
     */
    private function declareExchanges($exchange)
    {
        $this->declareExchange($exchange);
        $this->declareExchange(self::retryExchangeName($exchange));
        $this->declareExchange(self::deadLetterExchangeName($exchange));
    }

    /**
     * @param string $name
     */
    private function declareExchange($name)
    {
        $this->channel->exchange_declare($name, AMQPExchangeType::TOPIC, false, true);
    }

    /**
     * @param string $name
     * @return string
     */
    private static function retryExchangeName($name)
    {
        return sprintf('retry-%s', $name);
    }

    /**
     * @param string $name
     * @return string
     */
    private static function deadLetterExchangeName($name)
    {
        return sprintf('dead_letter-%s', $name);
    }

    /**
     * @param string $queue
     * @param string $exchange
     */
    private function declareQueues($queue, $exchange)
    {
        $this->declareQueue($queue);
        $this->declareQueue(self::retryQueueName($queue), new AMQPTable(array(
            "x-dead-letter-exchange" => $exchange,
            'x-dead-letter-routing-key' => $queue,
            "x-message-ttl" => 15000
        )));
        $this->declareQueue(self::deadLetterQueueName($queue));
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    private function declareQueue($name, $arguments = [])
    {
        $this->channel->queue_declare(
            $name, false, true, false, false, false, $arguments);
    }

    /**
     * @param string $name
     * @return string
     */
    private static function retryQueueName($name)
    {
        return sprintf('retry.%s', $name);
    }

    /**
     * @param string $name
     * @return string
     */
    private static function deadLetterQueueName($name)
    {
        return sprintf('dead_letter.%s', $name);
    }

    /**
     * @param string $queue
     * @param string $exchange
     * @param string $routingKey
     */
    private function declareBinds($queue, $exchange, $routingKey)
    {
        $this->declareBind($queue, $exchange, $routingKey);
        $this->declareBind($queue, $exchange, $queue);
        $this->declareBind(self::retryQueueName($queue), $exchange, $queue);
        $this->declareBind(self::deadLetterQueueName($queue), $exchange, $queue);
    }

    /**
     * @param string $queue
     * @param string $exchange
     * @param string $routingKey
     */
    private function declareBind($queue, $exchange, $routingKey)
    {
        $this->channel->queue_bind($queue, $exchange, $routingKey);
    }
}