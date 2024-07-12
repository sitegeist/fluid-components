<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromNull defines an alternative constructor
 * which initializes the object without any input
 */
interface ConstructibleFromNull
{
    /**
     * Creates an instance of the class
     */
    public static function fromNull(): object;
}
