<?php

namespace SMS\FluidComponents\Domain\Model;

use Exception;
use SMS\FluidComponents\Interfaces\ConstructibleFromDateTime;
use SMS\FluidComponents\Interfaces\ConstructibleFromDateTimeImmutable;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;

class DateTime extends \DateTime implements ConstructibleFromString, ConstructibleFromInteger, ConstructibleFromDateTime, ConstructibleFromDateTimeImmutable
{
    /**
     * Convert string input to datetime object
     *
     * @param string $value
     * @return DateTime|false
     * @throws Exception
     */
    public static function fromString(string $value)
    {
        return new static($value);
    }

    /**
     * Convert UNIX timestamp to datetime object
     *
     * @param int $value
     * @return self
     * @throws Exception
     */
    public static function fromInteger(int $value): self
    {
        return new static('@' . $value);
    }

    /**
     * Passes datetime object
     *
     * @param \DateTime $value
     * @return static
     * @throws Exception
     */
    public static function fromDateTime(\DateTime $value): self
    {
        return new static($value->format(\DateTimeInterface::RFC3339_EXTENDED));
    }

    /**
     * Passes immutable datetime object
     *
     * @param \DateTimeImmutable $value
     * @return static
     * @throws Exception
     */
    public static function fromDateTimeImmutable(\DateTimeImmutable $value): self
    {
        return new static($value->format(\DateTimeInterface::RFC3339_EXTENDED));
    }
}
