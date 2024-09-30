<?php declare(strict_types=1);

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Domain\Model\Slot;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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

    public function render(): mixed
    {
        $slotContent = $this->renderingContext->getVariableProvider()->get($this->arguments['name']);

        if (isset($slotContent) && !$slotContent instanceof Slot) {
            throw new InvalidArgumentException(
                sprintf('Slot "%s" cannot be rendered because it isn\'t a valid slot object.', $this->arguments['name']),
                1670247849
            );
        }

        if ((string)$slotContent === '') {
            return $this->arguments['default'] ?: $this->renderChildren();
        }

        return $slotContent;
    }
}
