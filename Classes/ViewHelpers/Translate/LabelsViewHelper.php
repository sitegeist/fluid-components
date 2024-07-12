<?php

namespace SMS\FluidComponents\ViewHelpers\Translate;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class LabelsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('keys', 'array', 'Array of translation keys; Can also contain subarrays, then "key" is key, "arguments" is an array of sprintf arguments, and "default" is a default value', true);
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
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
        $keys = $arguments['keys'];
        $extensionName = $arguments['extensionName'];

        $request = $renderingContext->getRequest();
        $extensionName = $extensionName ?? $request->getControllerExtensionName();

        $labels = [];
        foreach ($keys as $name => $translation) {
            if (is_array($translation)) {
                $translateArguments = $translation['arguments'] ?? [];
                $default = $translation['default'] ?? '';
                $translation = $translation['key'] ?? '';
            } else {
                $translateArguments = [];
                $default = '';
            }

            try {
                $value = static::translate($translation, $extensionName, $translateArguments, $arguments['languageKey'], $arguments['alternativeLanguageKeys']);
            } catch (\InvalidArgumentException $e) {
                $value = null;
            }
            if ($value === null) {
                $value = $default;
                if (!empty($translateArguments)) {
                    $value = vsprintf($value, $translateArguments);
                }
            }

            $labels[$name] = $value;
        }

        return $labels;
    }

    /**
     * Wrapper call to static LocalizationUtility
     *
     * @param string $id Translation Key compatible to TYPO3 Flow
     * @param string $extensionName UpperCamelCased extension key (for example BlogExample)
     * @param array $arguments Arguments to be replaced in the resulting string
     * @param string $languageKey Language key to use for this translation
     * @param string[] $alternativeLanguageKeys Alternative language keys if no translation does exist
     *
     * @return string|null
     */
    protected static function translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys)
    {
        return LocalizationUtility::translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }
}
