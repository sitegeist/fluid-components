<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Domain\Model\Slot;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class SlotViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of the slot that should be rendered', false, 'content');
        $this->registerArgument(
            'default',
            'string',
            'Default content that should be rendered if slot is not defined (falls back to tag content)',
            false,
            null,
            true
        );
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $slotContent = $renderingContext->getVariableProvider()->get($arguments['name']);

        if (isset($slotContent) && !$slotContent instanceof Slot) {
            throw new InvalidArgumentException(
                sprintf('Slot "%s" cannot be rendered because it isn\'t a valid slot object.', $arguments['name']),
                1670247849
            );
        }

        if ((string)$slotContent === '') {
            return $arguments['default'] ?: $renderChildrenClosure();
        }

        return $slotContent;
    }
}
