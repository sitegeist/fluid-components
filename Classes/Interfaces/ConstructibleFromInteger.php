<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromInteger defines an alternative constructor
 * which "converts" the provided integer to the class implementing
 * the interface
 */
interface ConstructibleFromInteger
{
    /**
     * Creates an instance of the class based on the provided integer
     *
     * @param integer $value
     * @return object
     */
    public static function fromInteger(int $value);
}
