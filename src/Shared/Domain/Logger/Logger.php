<?php

namespace Diaz\Shared\Domain\Logger;

/**
 * Class Logger
 * @package Diaz\Shared\Domain\Logger
 */
interface Logger
{
    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = []);

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, $context = []);
}