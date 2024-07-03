<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

class ContentViewHelper extends AbstractViewHelper
{
    use ParserRuntimeOnly;

    protected $escapeChildren = true;

    public function initializeArguments()
    {
        $this->registerArgument('slot', 'string', 'Slot name', false, ComponentRenderer::DEFAULT_SLOT);
    }
}
