<?php

namespace Diaz\Shared\Infrastructure\Monolog;

use Diaz\Shared\Domain\Logger\Logger;

/**
 * Class MonologLogger
 * @package Diaz\Shared\Infrastructure\Monolog
 */
class MonologLogger implements Logger
{
    /** @var Logger */
    private $monolog;

    /**
     * MonologLogger constructor.
     * @param \Monolog\Logger $logger
     */
    public function __construct(\Monolog\Logger $logger)
    {
        $this->monolog = $logger;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = [])
    {
        $this->monolog->info($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, $context = [])
    {
        $this->monolog->warning($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = [])
    {
        $this->monolog->error($message, $context);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, $context = [])
    {
        $this->monolog->log($level, $message, $context);
    }
}