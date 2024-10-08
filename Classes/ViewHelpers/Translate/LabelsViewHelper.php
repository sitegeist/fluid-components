<?php declare(strict_types=1);

namespace SMS\FluidComponents\ViewHelpers\Translate;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class LabelsViewHelper extends AbstractViewHelper
{
    /**
     * @throws Exception
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('keys', 'array', 'Array of translation keys; Can also contain subarrays, then "key" is key, "arguments" is an array of sprintf arguments, and "default" is a default value', true);
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
        $this->registerArgument('languageKey', 'string', 'Language key ("dk" for example) or "default" to use for this translation. If this argument is empty, we use the current language');
        // @deprecated will be removed in 4.0
        $this->registerArgument('alternativeLanguageKeys', 'array', 'Alternative language keys if no translation does exist');
    }

    public function render(): array
    {
        $keys = $this->arguments['keys'];
        $extensionName = $this->arguments['extensionName'] ?? $this->getRequest()->getControllerExtensionName();

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

            if ($this->arguments['alternativeLanguageKeys']) {
                trigger_error('Calling labels with the argument alternativeLanguageKeys will be removed in fluid-components 4.0', E_USER_DEPRECATED);
            }
            if ($this->arguments['languageKey']) {
                $localeFactory = GeneralUtility::makeInstance(Locales::class);
                $locale = $localeFactory->createLocale($this->arguments['languageKey'], $this->arguments['alternativeLanguageKeys']);
            }

            try {
                $value = LocalizationUtility::translate($translation, $extensionName, $translateArguments, $locale ?? null);
            } catch (InvalidArgumentException) {
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

    private function getRequest(): RequestInterface
    {
        if (method_exists($this->renderingContext, 'getAttribute') &&
            method_exists($this->renderingContext, 'hasAttribute') &&
            $this->renderingContext->hasAttribute(ServerRequestInterface::class)
        ) {
            return $this->renderingContext->getAttribute(ServerRequestInterface::class);
        } else {
            return $this->renderingContext->getRequest();
        }
    }
}
