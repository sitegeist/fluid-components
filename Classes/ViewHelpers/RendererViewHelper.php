<?php

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RendererViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        return $renderChildrenClosure();
    }
}
