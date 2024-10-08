<?php declare(strict_types=1);
namespace SMS\FluidComponents\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper adds one variable to the end of the array and returns the result.
 * Similar functionality as in v:iterator.push from VHS.
 *
 * <code title="Provides array of news tags">
 *   <f:variable name="tags"></f:variable>
 *   <f:for each="{newsItem.tags}" as="tag">
 *     <fc:variable.push name="tags" item="{tag.title}" />
 *   </f:for>
 * </code>
 *
 * @package SMS\FluidComponents\ViewHelpers\Variable
 *
 * @author Simon Praetorius <praetorius@sitegeist.de>
 */
class PushViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('item', 'mixed', 'Item to push to specified array variable. If not in arguments then taken from tag content');
        $this->registerArgument('name', 'string', 'Name of variable to extend', true);
        $this->registerArgument('key', 'string', 'Key that should be used in the array');
    }

    public function render(): void
    {
        $value = $this->arguments['item'] ?? $this->renderChildren();

        $variable = $this->renderingContext->getVariableProvider()->get($this->arguments['name']);
        if (!is_array($variable)) {
            $variable = [];
        }
        if ($this->arguments['key']) {
            $variable[$this->arguments['key']] = $value;
        } else {
            $variable[] = $value;
        }

        $this->renderingContext->getVariableProvider()->add($this->arguments['name'], $variable);
    }
}
