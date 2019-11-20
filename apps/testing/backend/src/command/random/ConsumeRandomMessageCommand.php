<?php

namespace Diaz\TestingBackend\command\random;

use Closure;
use Diaz\Shared\Infrastructure\RabbitMQ\RabbitMQConsumer;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Publicar mensaje random a Rabbit MQ')
            ->addArgument('throwException', InputArgument::OPTIONAL, 'Throw exception', '0');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $throwException = $input->getArgument('throwException') === '1';
        pipe(repeat($this->consume($throwException), 1), $this->consumer->close());
    }

    /**
     * @param bool $throwException
     * @return Closure
     */
    private function consume($throwException)
    {
        return function () use ($throwException) {
            apply($this->consumer, [
                    'testing.random_checked',
                    'diaz.random.event.random.checked',
                    function ($message) use ($throwException) {
                        if ($throwException) {
                            throw new Exception('error');
                        }
                    }]
            );
        };
    }
}