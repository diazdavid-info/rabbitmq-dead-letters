<?php

namespace Diaz\TestingBackend\command\random;

use Diaz\Random\Domain\RandomEventDomain;
use Diaz\Shared\Domain\Publisher\Publisher;
use Diaz\Shared\Domain\ValueObject\Uuid;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PublishRandomMessageCommand
 * @package Diaz\TestingBackend\command\random
 */
class PublishRandomMessageCommand extends Command
{
    /** @var Publisher */
    private $publisher;

    /**
     * PublishRandomMessageCommand constructor.
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        parent::__construct(null);
        $this->publisher = $publisher;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('testing:random:message_publish')
            ->setDescription('Publicar mensaje random a Rabbit MQ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->publisher->publish(
            new RandomEventDomain(
                Uuid::random()->value(),
                'Foo'
            )
        );
    }
}