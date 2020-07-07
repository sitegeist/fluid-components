<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * ConstructibleFromDateTime defines an alternative constructor
 * which "converts" the provided DateTime to the class implementing
 * the interface
 */
interface ConstructibleFromDateTime
{
    /**
     * Creates an instance of the class based on the provided DateTime
     *
     * @param \DateTime $value
     * @return object
     */
    public static function fromDateTime(\DateTime $value);
}
