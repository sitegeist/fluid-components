<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Domain\Model\ComponentInfo;
use SMS\FluidComponents\Domain\Model\ComponentInfoStack;
use SMS\FluidComponents\Domain\Model\Slot;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class ContentViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = true;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of the slot that should be targeted', true);
    }

    /*
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): void
    {
        $argumentName = $arguments['name'];
        $componentInfo = self::getComponentInfo($renderingContext, $argumentName);
        $componentInfo->arguments[$argumentName] = $renderChildrenClosure();
    }

    private static function getComponentInfo(RenderingContextInterface $renderingContext, string $argumentName): ComponentInfo
    {
        if ($argumentName === 'content') {
            throw new \UnexpectedValueException('Can not set reserved argument with name "content"', 1670437831);
        }
        $componentInfo = self::getComponentInfoStack($renderingContext)->current();
        if (!isset($componentInfo->argumentDefinitions[$argumentName])) {
            throw new \UnexpectedValueException(sprintf('No argument with name "%s" is found', $argumentName), 1670428377);
        }
        if (($argumentType = $componentInfo->argumentDefinitions[$argumentName]->getType()) !== Slot::class) {
            throw new \UnexpectedValueException(sprintf('Content view helper can only set arguments of type Slot, "%s" given', $argumentType), 1670434547);
        }

        return $componentInfo;
    }

    private static function getComponentInfoStack(RenderingContextInterface $renderingContext): ComponentInfoStack
    {
        if (!$renderingContext->getViewHelperVariableContainer()->exists(ComponentRenderer::class, ComponentRenderer::COMPONENT_INFO_STACK_KEY)
            || !($componentInfoStack = $renderingContext->getViewHelperVariableContainer()->get(ComponentRenderer::class, ComponentRenderer::COMPONENT_INFO_STACK_KEY)) instanceof ComponentInfoStack
        ) {
            throw new \UnexpectedValueException('ContentViewHelper must be called from within a Fluid component', 1670433285);
        }

        return $componentInfoStack;
    }
}
