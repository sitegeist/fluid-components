<?php declare(strict_types=1);

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromArray defines an alternative constructor
 * which "converts" the provided array to the class implementing
 * the interface.
 */
interface ConstructibleFromArray
{
    /**
     * Creates an instance of the class based on the provided array.
     */
    public static function fromArray(array $value): ?object;
}
