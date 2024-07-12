<?php
namespace SMS\FluidComponents\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * This ViewHelper adds one variable to the end of the array and returns the result.
 * Similar functionality as in v:iterator.push from VHS
 *
 * <code title="Provides array of news tags">
 *   <f:variable name="tags"></f:variable>
 *   <f:for each="{newsItem.tags}" as="tag">
 *     <fc:variable.push name="tags" item="{tag.title}" />
 *   </f:for>
 * </code>
 *
 * @package SMS\FluidComponents\ViewHelpers\Variable
 * @author Simon Praetorius <praetorius@sitegeist.de>
 */
class PushViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('item', 'mixed', 'Item to push to specified array variable. If not in arguments then taken from tag content');
        $this->registerArgument('name', 'string', 'Name of variable to extend', true);
        $this->registerArgument('key', 'string', 'Key that should be used in the array');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): void {
        $value = $arguments['item'] ?? $renderChildrenClosure();

        $variable = $renderingContext->getVariableProvider()->get($arguments['name']);
        if (!is_array($variable)) {
            $variable = [];
        }
        if ($arguments['key']) {
            $variable[$arguments['key']] = $value;
        } else {
            $variable[] = $value;
        }

        $renderingContext->getVariableProvider()->add($arguments['name'], $variable);
    }
}
