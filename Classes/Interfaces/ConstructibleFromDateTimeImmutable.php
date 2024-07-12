<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromDateTimeImmutable defines an alternative constructor
 * which "converts" the provided DateTime to the class implementing
 * the interface
 */
interface ConstructibleFromDateTimeImmutable
{
    /**
     * Creates an instance of the class based on the provided DateTimeImmutable
     */
    public static function fromDateTimeImmutable(\DateTimeImmutable $value): object;
}
