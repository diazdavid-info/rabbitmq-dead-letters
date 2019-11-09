<?php

use DI\Container;
use Diaz\Shared\Domain\Logger\Logger;
use Diaz\Shared\Domain\Publisher\Publisher;
use Diaz\Shared\Infrastructure\Monolog\MonologLogger;
use Diaz\Shared\Infrastructure\RabbitMQ\RabbitMQConnection;
use Diaz\Shared\Infrastructure\RabbitMQ\RabbitMQConsumer;
use Diaz\Shared\Infrastructure\RabbitMQ\RabbitMQPublisher;
use Diaz\TestingBackend\command\configuration\RabbitConfigurationCommand;
use Diaz\TestingBackend\command\random\ConsumeRandomMessageCommand;
use Diaz\TestingBackend\command\random\PublishRandomMessageCommand;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\ErrorLogHandler;
use Psr\Container\ContainerInterface;

return static function (Container $container) {

    $container->set(PublishRandomMessageCommand::class, function (ContainerInterface $container) {
        return new PublishRandomMessageCommand(
            $container->get(Publisher::class)
        );
    });

    $container->set(Publisher::class, function (ContainerInterface $container) {
        return new RabbitMQPublisher(
            $container->get(RabbitMQConnection::class),
            'domain_event',
            $container->get(Logger::class)
        );
    });

    $container->set(RabbitMQConnection::class, function () {
        return new RabbitMQConnection(
            getenv('RABBIT_MQ_HOST'),
            getenv('RABBIT_MQ_PORT'),
            getenv('RABBIT_MQ_USER'),
            getenv('RABBIT_MQ_PASS'),
            getenv('RABBIT_MQ_VHOST')
        );
    });

    $container->set(Logger::class, function () {
        $logger = new \Monolog\Logger('logger');
        $stdoutHandler = new ErrorLogHandler();
        $formatter = new JsonFormatter();
        $stdoutHandler->setFormatter($formatter);
        $logger->pushHandler($stdoutHandler);

        return new MonologLogger($logger);
    });

    $container->set(ConsumeRandomMessageCommand::class, function (ContainerInterface $container) {
        return new ConsumeRandomMessageCommand(
            $container->get(RabbitMQConsumer::class)
        );
    });

    $container->set(RabbitMQConsumer::class, function (ContainerInterface $container) {
        return new RabbitMQConsumer(
            $container->get(RabbitMQConnection::class),
            'domain_event',
            $container->get(Logger::class)
        );
    });

    $container->set(RabbitConfigurationCommand::class, function (ContainerInterface $container) {
        return new RabbitConfigurationCommand(
            $container->get(RabbitMQConnection::class)
        );
    });

};