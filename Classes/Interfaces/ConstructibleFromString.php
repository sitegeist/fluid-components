<?php declare(strict_types=1);

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromString defines an alternative constructor
 * which "converts" the provided string to the class implementing
 * the interface.
 */
interface ConstructibleFromString
{
    /**
     * Creates an instance of the class based on the provided string.
     *
     * @param string $value
     *
     * @return object
     */
    public static function fromString(string $value);
}
