<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ConstructibleFromFloat;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;

class FloatType implements ConstructibleFromString, ConstructibleFromInteger, ConstructibleFromFloat
{
    public static function fromString(string $value): float
    {
        return (float)$value;
    }

    public static function fromInteger(int $value): float
    {
        return (float)$value;
    }

    public static function fromFloat(float $value): float
    {
        return $value;
    }
}
