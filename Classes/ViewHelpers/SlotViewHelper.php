<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Domain\Model\Slot;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class SlotViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
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

    /*
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $slotContent = $renderingContext->getVariableProvider()->get($arguments['name']);

        if (!$slotContent instanceof Slot) {
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
