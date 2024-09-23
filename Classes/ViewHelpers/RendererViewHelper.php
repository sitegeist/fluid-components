<?php declare(strict_types=1);

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RendererViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function render(): mixed
    {
        return $this->renderChildren();
    }
}
