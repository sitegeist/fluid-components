<?php

namespace SMS\FluidComponents\Interfaces;

interface ConstructibleFromClosure
{
    public static function fromClosure(\Closure $value);
}
