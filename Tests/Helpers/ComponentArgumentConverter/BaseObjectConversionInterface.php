<?php declare(strict_types=1);
namespace SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter;

interface BaseObjectConversionInterface
{
    public static function fromBaseObject(BaseObject $value);
}
