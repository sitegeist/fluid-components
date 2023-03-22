<?php
namespace SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter;

class DummyValue implements DummyConversionInterface, BaseObjectConversionInterface
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

    public static function fromBaseObject(BaseObject $object)
    {
        return new static($object->value);
    }
}
