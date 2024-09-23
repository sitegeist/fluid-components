<?php declare(strict_types=1);

namespace SMS\FluidComponents\Interfaces;

use Closure;

interface ConstructibleFromClosure
{
    public static function fromClosure(Closure $value);
}
