<?php

namespace Diaz\TestingBackend\command\random;

use Closure;
use Diaz\Shared\Infrastructure\RabbitMQ\RabbitMQConsumer;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Lambdish\Phunctional\apply;
use function Lambdish\Phunctional\pipe;
use function Lambdish\Phunctional\repeat;

/**
 * Class ConsumeRandomMessageCommand
 * @package Diaz\TestingBackend\command\random
 */
class ConsumeRandomMessageCommand extends Command
{
    /** @var RabbitMQConsumer */
    private $consumer;

    /**
     * ConsumeRandomMessageCommand constructor.
     * @param RabbitMQConsumer $consumer
     */
    public function __construct(RabbitMQConsumer $consumer)
    {
        parent::__construct(null);
        $this->consumer = $consumer;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('testing:random:message_consume')
            ->setDescription('Publicar mensaje random a Rabbit MQ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        pipe(repeat($this->consume(), 1), $this->consumer->close());
    }

    /**
     * @return Closure
     */
    private function consume()
    {
        return function () {
            apply($this->consumer, [
                    'testing.random_checked',
                    'diaz.random.event.random.checked',
                    function ($message) {
                        // ...
                    }]
            );
        };
    }
}