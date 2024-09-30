<?php declare(strict_types=1);
namespace SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter;

interface DummyConversionInterface
{
    public static function fromString(string $value);
}
