<?php
namespace SMS\FluidComponents\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class PushViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('item', 'mixed', 'Item to push to specified array variable. If not in arguments then taken from tag content');
        $this->registerArgument('name', 'string', 'Name of variable to extend', true);
        $this->registerArgument('key', 'string', 'Key that should be used in the array');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return null
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $value = $arguments['item'] ?? $renderChildrenClosure();

        $variable = $renderingContext->getVariableProvider()->get($arguments['name']);
        if (!is_array($variable)) {
            $variable = [];
        }
        if ($arguments['key']) {
            $variable[$arguments['key']] = $value;
        } else {
            //array_push($variable, $value);
            $variable[] = $value;
        }

        $renderingContext->getVariableProvider()->add($arguments['name'], $variable);
    }
}
