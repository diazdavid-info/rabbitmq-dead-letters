<?php

namespace Diaz\Shared\Domain\ValueObject;

use Exception;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

/**
 * Class Uuid
 * @package Diaz\Shared\Domain\ValueObject
 */
class Uuid
{
    /** @var string */
    private $value;

    /**
     * Uuid constructor.
     * @param string $value
     */
    public function __construct($value)
    {
        $this->ensureIsValidUuid($value);

        $this->value = $value;
    }

    /**
     * @param string $id
     */
    private function ensureIsValidUuid($id)
    {
        if (!RamseyUuid::isValid($id)) {
            throw new InvalidArgumentException(
                sprintf(
                    '<%s> does not allow the value <%s>.',
                    static::class,
                    is_scalar($id) ? $id : gettype($id))
            );
        }
    }

    /**
     * @return Uuid
     * @throws Exception
     */
    public static function random()
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value();
    }

    /**
     * @return string
     */
    public function value()
    {
        return $this->value;
    }
}
