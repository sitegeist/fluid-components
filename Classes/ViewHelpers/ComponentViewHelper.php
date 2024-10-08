<?php declare(strict_types=1);

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ComponentViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('description', 'string', 'Description of the component');
    }

    public function render(): string
    {
        return trim((string) $this->renderChildren());
    }
}
