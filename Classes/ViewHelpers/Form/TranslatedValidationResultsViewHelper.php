<?php
namespace SMS\FluidComponents\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\ValidationResultsViewHelper;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * This ViewHelper provides translated validation result messages. When used with EXT:form, the already existing
 * translation chain will be used. Within ExtBase, a translate prefix can be defined which will be prepended
 * to the validation error code in question. The result is an array of errors/warnings/notices which can be used
 * by Fluid Components without any special knowledge or dependency on TYPO3's data structures.
 *
 * <code title="Provides translated validation results">
 *   <fc:form.translatedValidationResults element="{element}" as="validationResults">
 *     <f:for each="{validationResults.errors}" as="error">
 *       {error.message}<br />
 *     </f:for>
 *     <!-- or -->
 *     <my:molecule.messageBox messages="{validationResults.errors}" />
 *   </fc:form.translatedValidationResults>
 * </code>
 *
 * @package SMS\FluidComponents\ViewHelpers\Form
 * @author Simon Praetorius <praetorius@sitegeist.de>
 */
class TranslatedValidationResultsViewHelper extends ValidationResultsViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('translatePrefix', 'string', 'String that should be prepended to every language key (e. g. "forms.validation."); Will be ignored if $element is set.', false, '');
        $this->registerArgument('element', RootRenderableInterface::class, 'Form Element to translate');
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
        $this->registerArgument('languageKey', 'string', 'Language key ("dk" for example) or "default" to use for this translation. If this argument is empty, we use the current language');
        $this->registerArgument('alternativeLanguageKeys', 'array', 'Alternative language keys if no translation does exist');
    }

    /**
     * Provides and translates validation results for the specified form field
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $controllerContext = $renderingContext->getControllerContext();
        $extensionName = $controllerContext->getRequest()->getControllerExtensionName();

        $for = $arguments['for'];
        $element = $arguments['element'];

        $translatedResults = [
            'errors' => [],
            'warnings' => [],
            'notices' => []
        ];

        // Generate validation selector based on form element
        if ($element) {
            $for = $element->getRootForm()->getIdentifier() . '.' . $element->getIdentifier();
        }

        // Fetch validation results from API
        $validationResults = $controllerContext->getRequest()->getOriginalRequestMappingResults();
        if ($validationResults !== null && $for !== '') {
            $validationResults = $validationResults->forProperty($for);
        }

        // Translate validation results
        if ($validationResults) {
            $levels = [
                'errors' => $validationResults->getErrors(),
                'warnings' => $validationResults->getWarnings(),
                'notices' => $validationResults->getNotices()
            ];
            foreach ($levels as $level => $messages) {
                foreach ($messages as $message) {
                    if ($element) {
                        // Use form framework for translation
                        $translatedMessage = static::translateFormElementError(
                            $element,
                            $message->getCode(),
                            $message->getArguments(),
                            $message->getMessage(),
                            $renderingContext
                        );
                    } else {
                        // Use TYPO3 for translation
                        $translatedMessage = static::translate(
                            $arguments['translatePrefix'] . $message->getCode(),
                            $arguments['extensionName'] ?? $extensionName,
                            $message->getArguments(),
                            $arguments['languageKey'],
                            $arguments['alternativeLanguageKeys']
                        );
                        $translatedMessage = $translatedMessage ?? $message->getMessage();
                    }

                    $translatedResults[$level][] = [
                        'message' => $translatedMessage,
                        'originalMessage' => $message->getMessage(),
                        'code' => $message->getCode(),
                        'title' => $message->getTitle(),
                        'arguments' => $message->getArguments()
                    ];
                }
            }
        }

        $translatedResults['hasErrors'] = !empty($translatedResults['errors']);
        $translatedResults['hasWarnings'] = !empty($translatedResults['warnings']);
        $translatedResults['hasNotices'] = !empty($translatedResults['notices']);

        $templateVariableContainer->add($arguments['as'], $translatedResults);
        $output = $renderChildrenClosure();
        $templateVariableContainer->remove($arguments['as']);

        return $output;
    }

    /**
     * Returns the localized label of the LOCAL_LANG key, $key.
     *
     * @param string $key The key from the LOCAL_LANG array for which to return the value.
     * @param string|null $extensionName The name of the extension
     * @param array $arguments The arguments of the extension, being passed over to vsprintf
     * @param string $languageKey The language key or null for using the current language from the system
     * @param string[] $alternativeLanguageKeys The alternative language keys if no translation was found. If null and we are in the frontend, then the language_alt from TypoScript setup will be used
     * @return string|null The value from LOCAL_LANG or null if no translation was found.
     */
    public static function translate(
        string $key,
        string $extensionName = null,
        array $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ): ?string {
        return LocalizationUtility::translate($key, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }

    /**
     * Translates the provided validation message by using the translation chain by EXT:form
     *
     * @param RootRenderableInterface $element
     * @param int $code
     * @param string $defaultValue
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function translateFormElementError(
        RootRenderableInterface $element,
        int $code,
        array $arguments,
        string $defaultValue = '',
        RenderingContextInterface $renderingContext
    ): string {
        /** @var FormRuntime $formRuntime */
        $formRuntime = $renderingContext
            ->getViewHelperVariableContainer()
            ->get(RenderRenderableViewHelper::class, 'formRuntime');

        return TranslationService::getInstance()->translateFormElementError(
            $element,
            $code,
            $arguments,
            $defaultValue,
            $formRuntime
        );
    }
}
