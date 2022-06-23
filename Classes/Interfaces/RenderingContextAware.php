<?php

namespace SMS\FluidComponents\Interfaces;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * By implementing RenderingContextAware in a data structure, fluid
 * components will provide the current fluid rendering context to the
 * data structure when used in a component call
 */
interface RenderingContextAware
{
    public function setRenderingContext(RenderingContextInterface $renderingContext): void;
}
