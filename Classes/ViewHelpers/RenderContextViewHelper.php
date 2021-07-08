<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\ViewHelpers\RendererViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderContextViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Renderer name', true);
    }

    /*
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $parentRenderer = $viewHelperVariableContainer->get(RendererViewHelper::class, 'renderer');

        $viewHelperVariableContainer->add(RendererViewHelper::class, 'renderer', $arguments['name']);
        $content = $renderChildrenClosure();
        $viewHelperVariableContainer->add(RendererViewHelper::class, 'renderer', $parentRenderer);

        return $content;
    }
}
