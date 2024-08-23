<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ConstructibleFromClosure;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;
use SMS\FluidComponents\Interfaces\EscapedParameter;

/**
 * Data Structure to encapsulate html markup provided to a component
 */
class Slot implements EscapedParameter, ConstructibleFromString, ConstructibleFromClosure, \Countable
{
    protected $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public static function fromString(string $html): Slot
    {
        return new Slot($html);
    }

    public static function fromClosure(\Closure $closure): Slot
    {
        return new Slot($closure());
    }

    public function count(): int
    {
        return strlen((string) $this->html);
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
