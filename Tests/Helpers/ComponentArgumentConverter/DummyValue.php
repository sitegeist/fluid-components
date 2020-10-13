<?php
namespace SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter;

class DummyValue implements DummyConversionInterface
{
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value)
    {
        return new static($value);
    }
}
