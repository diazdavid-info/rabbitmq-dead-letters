<?php

namespace Diaz\Shared\Domain\Publisher;

/**
 * Interface Publisher
 * @package Diaz\Shared\Domain\Publisher
 */
interface Publisher
{
    /**
     * @param Message $message
     */
    public function publish(Message $message);
}
