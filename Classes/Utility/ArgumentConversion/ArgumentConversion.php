<?php
declare(strict_types=1);
namespace SMS\FluidComponents\Utility\ArgumentConversion;

use SMS\FluidComponents\Utility\ArgumentConversion\ArgumentConversionInterface;
use SMS\FluidComponents\Utility\ArgumentConversion\ArgumentConversionDefinition;

class ArgumentConversion implements ArgumentConversionInterface
{
    private ArgumentConversionDefinition $definition;
    private string $targetType;

    public function __construct(ArgumentConversionDefinition $definition, string $targetType)
    {
        if (!$definition->isApplicable($targetType)) {
            throw new \InvalidArgumentException('Target type must be compatible with source type', 1679942496);
        }
        $this->definition = $definition;
        $this->targetType = $targetType;
    }

    public function convert($value)
    {
        return $this->targetType::{$this->definition->factoryMethod}($value);
    }
}
