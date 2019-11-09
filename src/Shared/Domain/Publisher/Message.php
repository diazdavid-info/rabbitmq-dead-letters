<?php

namespace Diaz\Shared\Domain\Publisher;

use DateTime;
use Diaz\Shared\Domain\ValueObject\Uuid;
use Exception;

/**
 * Class Message
 * @package Diaz\Shared\Domain\Publisher
 */
abstract class Message
{
    /** @var string */
    private $messageId;
    /** @var string */
    private $occurredOn;

    /**
     * Message constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->messageId = Uuid::random()->value();
        $this->occurredOn = (new DateTime('now'))->format('Y-m-d H:i:s');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge([
            'message_id' => $this->messageId,
            'occurred_on' => $this->occurredOn,
        ], $this->toPrimitive());
    }

    /**
     * @return array
     */
    abstract public function toPrimitive();

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getOccurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * @return string
     */
    abstract public function type();
}
