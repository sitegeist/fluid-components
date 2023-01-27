<?php
namespace SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter;

class BaseObject
{
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
