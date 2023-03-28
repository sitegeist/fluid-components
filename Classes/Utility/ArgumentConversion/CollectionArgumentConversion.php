<?php
declare(strict_types=1);
namespace SMS\FluidComponents\Utility\ArgumentConversion;

use SMS\FluidComponents\Utility\ArgumentConversion\ArgumentConversionDefinition;
use SMS\FluidComponents\Utility\ArgumentConversion\ArgumentConversionInterface;

class CollectionArgumentConversion implements ArgumentConversionInterface
{
    private IterableConversionDefinition $definition;
    private ArgumentConversionInterface $innerConversion;
    private int $depth;

    public function __construct(IterableConversionDefinition $definition, ArgumentConversionInterface $innerConversion, int $depth = 1)
    {
        $this->definition = $definition;
        $this->innerConversion = $innerConversion;
        $this->depth = $depth;
    }

    public function convert($value, int $depth = 1)
    {
        if ($depth > 0) {
            return array_map($value, function ($item) {
                if (!$this->definition->isApplicable($targetType)) {
                    // TODO error handling
                    // throw new \Exception();
                }
                return $this->convert($item, $depth - 1);
            });
        } else {
            return array_map($value, function ($item) {
                // TODO use inner definition
                if (!$this->definition->isApplicable($targetType)) {
                    // TODO error handling
                    // throw new \Exception();
                }
                return $this->innerConversion->convert($item);
            });
        }
    }
}
