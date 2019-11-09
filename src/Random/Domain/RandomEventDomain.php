<?php

namespace Diaz\Random\Domain;

use Diaz\Shared\Domain\Publisher\Message;
use Exception;

/**
 * Class RandomEventDomain
 * @package Diaz\Random\Domain
 */
class RandomEventDomain extends Message
{
    const TYPE = 'diaz.random.event.random.checked';

    /** @var string */
    private $id;
    /** @var string */
    private $name;

    /**
     * RandomEventDomain constructor.
     * @param string $id
     * @param string $name
     * @throws Exception
     */
    public function __construct($id, $name)
    {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function toPrimitive()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }

    /**
     * @return string
     */
    public function type()
    {
        return self::TYPE;
    }
}