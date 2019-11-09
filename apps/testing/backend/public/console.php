<?php

use DI\ContainerBuilder;
use Diaz\TestingBackend\command\configuration\RabbitConfigurationCommand;
use Diaz\TestingBackend\command\random\ConsumeRandomMessageCommand;
use Diaz\TestingBackend\command\random\PublishRandomMessageCommand;
use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

require __DIR__ . '/../../../../vendor/autoload.php';

$dotenv = Dotenv::create(__DIR__ . '/../../../../environments');
$dotenv->load();

$builder = new ContainerBuilder();
$container = $builder->build();

$dependencies = require __DIR__ . '/../config/di/dependencies.php';
$dependencies($container);


try {
    $application = new Application();
    $application->add($container->get(PublishRandomMessageCommand::class));
    $application->add($container->get(ConsumeRandomMessageCommand::class));
    $application->add($container->get(RabbitConfigurationCommand::class));
    $application->run();
    exit(0);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}