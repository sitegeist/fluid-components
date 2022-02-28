<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Utility\ComponentContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ContextViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('languageKey', 'string', 'Language key ("dk" for example) or "default" to use for this translation. If this argument is empty, we use the current language');
        $this->registerArgument('alternativeLanguageKeys', 'array', 'Alternative language keys if no translation does exist');
    }

    /*
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        // Create new component context based on ViewHelper arguments
        $componentContext = GeneralUtility::makeInstance(ComponentContext::class);
        if (isset($arguments['languageKey'])) {
            $componentContext->setLanguageKey($arguments['languageKey']);
        }
        if (isset($arguments['alternativeLanguageKeys'])) {
            $componentContext->setAlternativeLanguageKeys($arguments['alternativeLanguageKeys']);
        }

        // Merge with parent context
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $parentContext = $viewHelperVariableContainer->get(ComponentRenderer::class, 'componentContext');
        if ($parentContext) {
            $componentContext->applyDefaultsFromParentContext($parentContext);
        }

        // Render children with new context and reset context afterwards
        $viewHelperVariableContainer->add(ComponentRenderer::class, 'componentContext', $componentContext);
        $content = $renderChildrenClosure();
        $viewHelperVariableContainer->add(ComponentRenderer::class, 'componentContext', $parentContext);

        return $content;
    }
}
