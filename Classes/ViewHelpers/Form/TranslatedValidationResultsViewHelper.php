<?php
namespace SMS\FluidComponents\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Error\Message;
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
     * Stores message objects that have already been translated
     *
     * @var array
     */
    protected static $translatedMessagesCache = [];

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('translatePrefix', 'string', 'String that should be prepended to every language key; Will be ignored if $element is set.', false, 'validation.error.');
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

        $extensionName = $arguments['extensionName'] ?? $controllerContext->getRequest()->getControllerExtensionName();
        $for = rtrim($arguments['for'], '.');
        $element = $arguments['element'];

        $translatedResults = [
            'errors' => [],
            'warnings' => [],
            'notices' => [],
            'flattenedErrors' => [],
            'flattenedWarnings' => [],
            'flattenedNotices' => [],
            'hasErrors' => false,
            'hasWarnings' => false,
            'hasNotices' => false
        ];

        if ($element) {
            // Generate validation selector based on EXT:form element
            $for = $element->getRootForm()->getIdentifier() . '.' . $element->getIdentifier();
            $translatePrefix = '';
        } else {
            // Generate static language prefix for validation translations outsite of EXT:form
            $translatePrefix = ($arguments['translatePrefix']) ? rtrim($arguments['translatePrefix'], '.') . '.' : '';
            $translatePrefix .= ($for) ? $for . '.' : '';
        }

        // Fetch validation results from API
        $validationResults = $controllerContext->getRequest()->getOriginalRequestMappingResults();
        if ($validationResults !== null && $for !== '') {
            $validationResults = $validationResults->forProperty($for);
        }

        // Translate validation results
        if ($validationResults) {
            // Translate validation messages that refer to the current form field
            $levels = [
                'errors' => $validationResults->getErrors(),
                'warnings' => $validationResults->getWarnings(),
                'notices' => $validationResults->getNotices()
            ];
            foreach ($levels as $level => $messages) {
                foreach ($messages as $message) {
                    $translatedResults[$level][] = static::translateMessage(
                        $renderingContext,
                        $message,
                        $translatePrefix,
                        $element,
                        $extensionName,
                        $arguments['languageKey'],
                        $arguments['alternativeLanguageKeys']
                    );
                }
            }

            // Translate validation messages that refer to child fields (flattenedErrors)
            $levels = [
                'flattenedErrors' => $validationResults->getFlattenedErrors(),
                'flattenedWarnings' => $validationResults->getFlattenedWarnings(),
                'flattenedNotices' => $validationResults->getFlattenedNotices()
            ];
            foreach ($levels as $level => $flattened) {
                foreach ($flattened as $identifier => $messages) {
                    $translatedResults[$level][$identifier] = [];
                    foreach ($messages as $message) {
                        $translatedResults[$level][$identifier][] = static::translateMessage(
                            $renderingContext,
                            $message,
                            $translatePrefix . $identifier . '.',
                            $element,
                            $extensionName,
                            $arguments['languageKey'],
                            $arguments['alternativeLanguageKeys']
                        );
                    }
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
     * Translates a validation message, either by using EXT:form's translation chain
     * or by the custom implementation of fluid_components for validation translations
     *
     * @param RenderingContextInterface $renderingContext
     * @param Message $message
     * @param string $translatePrefix
     * @param RootRenderableInterface $element
     * @param string $extensionName
     * @param string $languageKey
     * @param array $alternativeLanguageKeys
     * @return void
     */
    protected static function translateMessage(
        RenderingContextInterface $renderingContext,
        Message $message,
        string $translatePrefix = '',
        RootRenderableInterface $element = null,
        string $extensionName = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ) {
        // Make sure that messages are translated only once
        $hash = spl_object_hash($message);
        if (isset(static::$translatedMessagesCache[$hash])) {
            return static::$translatedMessagesCache[$hash];
        }

        if ($element) {
            // Use EXT:form for translation
            $translatedMessage = static::translateFormElementError(
                $renderingContext,
                $element,
                $message->getCode(),
                $message->getArguments(),
                $message->getMessage()
            );
        } else {
            // Use TYPO3 for translation
            $translatedMessage = static::translateValidationError(
                [$translatePrefix],
                $message->getCode(),
                $message->getArguments(),
                $message->getMessage(),
                $extensionName,
                $languageKey,
                $alternativeLanguageKeys
            );
        }

        // Create new message object from the translated message
        $messageClass = get_class($message);
        $newMessage = new $messageClass(
            $translatedMessage,
            $message->getCode(),
            $message->getArguments(),
            $message->getTitle()
        );

        // Prevent double translations
        self::$translatedMessagesCache[$hash] = $newMessage;
        self::$translatedMessagesCache[spl_object_hash($newMessage)] = $newMessage;

        return $newMessage;
    }

    /**
     * Translates the provided validation message by using TYPO3's localization utility
     *
     * @param array $translationChain Chain of translation keys that should be checked for translations
     * @param int $code Validation error code
     * @param array $arguments The arguments of the extension, being passed over to vsprintf
     * @param string $defaultValue Default validation message used as a fallback
     * @param string|null $extensionName The name of the extension
     * @param string $languageKey The language key or null for using the current language from the system
     * @param string[] $alternativeLanguageKeys The alternative language keys if no translation was found. If null and we are in the frontend, then the language_alt from TypoScript setup will be used
     * @return string|null The value from LOCAL_LANG or null if no translation was found.
     */
    public static function translateValidationError(
        array $translationChain,
        int $code,
        array $arguments,
        string $defaultValue = '',
        string $extensionName = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ): ?string {
        foreach ($translationChain as $translatePrefix) {
            $translatedMessage = LocalizationUtility::translate(
                $translatePrefix . $code,
                $extensionName,
                $arguments,
                $languageKey,
                $alternativeLanguageKeys
            );
            if ($translatedMessage) {
                return $translatedMessage;
            }
        }
        return $defaultValue;
    }

    /**
     * Translates the provided validation message by using the translation chain by EXT:form
     *
     * @param RenderingContextInterface $renderingContext
     * @param RootRenderableInterface $element
     * @param int $code
     * @param string $defaultValue
     * @param array $arguments
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function translateFormElementError(
        RenderingContextInterface $renderingContext,
        RootRenderableInterface $element,
        int $code,
        array $arguments,
        string $defaultValue = ''
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
