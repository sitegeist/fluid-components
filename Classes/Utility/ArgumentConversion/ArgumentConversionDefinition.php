<?php
declare(strict_types=1);
namespace SMS\FluidComponents\Utility\ArgumentConversion;

class ArgumentConversionDefinition
{
    public string $sourceType;
    public string $interface;
    public string $factoryMethod;

    public function __construct(string $sourceType, string $interface, string $factoryMethod)
    {
        $this->sourceType = $sourceType;
        $this->interface = $interface;
        $this->factoryMethod = $factoryMethod;
    }

    public function isApplicable(string $targetType): bool
    {
        return is_subclass_of($targetType, $this->interface);
    }

    public function getFactoryMethod(): string
    {
        return $this->factoryMethod;
    }
}
