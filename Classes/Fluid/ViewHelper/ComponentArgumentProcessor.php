<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Fluid\ViewHelper;

use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentProcessorInterface;

/**
 * Decorates the core argument processor (Strict/LenientArgumentProcessor) with
 * fluid-components' own type conversion (ConstructibleFromString/FromArray/
 * FromClosure/... interfaces, see ComponentArgumentConverter). Since Fluid 5,
 * argument validation happens in ViewHelperInvoker via ArgumentProcessorInterface
 * before a ViewHelper is ever invoked, so ComponentRenderer/ParamViewHelper never
 * get a chance to run this conversion themselves anymore - it has to happen here.
 */
final readonly class ComponentArgumentProcessor implements ArgumentProcessorInterface
{
    public function __construct(
        private ArgumentProcessorInterface $decoratedProcessor,
        private ComponentArgumentConverter $componentArgumentConverter,
    ) {
    }

    public function process(mixed $value, ArgumentDefinition $definition): mixed
    {
        $value = $this->componentArgumentConverter->convertValueToType($value, $definition->getType());
        return $this->decoratedProcessor->process($value, $definition);
    }

    public function isValid(mixed $value, ArgumentDefinition $definition): bool
    {
        return $this->decoratedProcessor->isValid($value, $definition);
    }
}
