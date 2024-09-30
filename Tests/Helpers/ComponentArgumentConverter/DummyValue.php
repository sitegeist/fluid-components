<?php declare(strict_types=1);
namespace SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter;
use Override;

class DummyValue implements DummyConversionInterface, BaseObjectConversionInterface
{
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    #[Override]
    public static function fromString(string $value)
    {
        return new static($value);
    }

    #[Override]
    public static function fromBaseObject(BaseObject $object)
    {
        return new static($object->value);
    }
}
