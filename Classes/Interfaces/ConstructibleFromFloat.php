<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromFloat defines an alternative constructor
 * which "converts" the provided float to the class implementing
 * the interface.
 *
 * The reason for this is because of php gettype() returning the string 'double' for float values for historical reasons
 */
interface ConstructibleFromFloat
{
    /**
     * Creates an instance of the class based on the provided integer
     *
     * @param float $value
     * @return object
     */
    public static function fromFloat(float $value);
}
