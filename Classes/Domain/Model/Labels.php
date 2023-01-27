<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Interfaces\ComponentAware;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromNull;
use SMS\FluidComponents\Interfaces\RenderingContextAware;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class Labels implements ComponentAware, RenderingContextAware, \ArrayAccess, ConstructibleFromArray, ConstructibleFromNull
{
    const OVERRIDE_LANGUAGE_KEY = 'languageKey';
    const OVERRIDE_LANGUAGE_ALTERNATIVES = 'alternativeLanguageKeys';

    /**
     * Namespace of the current component
     *
     * @var string
     */
    protected $componentNamespace;

    /**
     * Fluid rendering context
     *
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * Static label values that should override those defined in language files
     *
     * @var array
     */
    protected $overrideLabels = [];

    /**
     * Cache for component labels file
     *
     * @var string
     */
    protected $labelsFile;

    /**
     * Constructor
     *
     * @param array $overrideLabels
     */
    public function __construct(array $overrideLabels = [])
    {
        $this->overrideLabels = $overrideLabels;
    }

    /**
     * Generate object based on an array passed to the component
     *
     * @param array $overrideLabels
     * @return self
     */
    public static function fromArray(array $overrideLabels): self
    {
        return new self($overrideLabels);
    }

    /**
     * Generate object, even if component parameter is optional and omitted
     *
     * @return self
     */
    public static function fromNull(): self
    {
        return new self;
    }

    /**
     * Receive component context to determine language file path
     *
     * @param string $componentNamespace
     * @return void
     */
    public function setComponentNamespace(string $componentNamespace): void
    {
        $this->componentNamespace = $componentNamespace;
    }

    /**
     * Receive current fluid rendering context
     *
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Check if language label is defined
     *
     * @param mixed $identifier
     * @return boolean
     */
    public function offsetExists($identifier): bool
    {
        return $this->offsetGet($identifier) !== null;
    }

    /**
     * Return value of language label
     *
     * @param mixed $identifier
     * @return string|null
     */
    public function offsetGet($identifier): ?string
    {
        if (isset($this->overrideLabels[$identifier])) {
            return $this->overrideLabels[$identifier];
        }

        // Check if an alternative language was specified for the component
        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
        if ($viewHelperVariableContainer->exists(ComponentRenderer::class, self::OVERRIDE_LANGUAGE_KEY)) {
            $languageKey = $viewHelperVariableContainer->get(ComponentRenderer::class, self::OVERRIDE_LANGUAGE_KEY);
            $alternativeLanguageKeys = $viewHelperVariableContainer->get(ComponentRenderer::class, self::OVERRIDE_LANGUAGE_ALTERNATIVES);
        } else {
            $languageKey = $alternativeLanguageKeys = null;
        }
        
        if (!isset($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
        }

        return LocalizationUtility::translate(
            $this->generateLabelIdentifier($identifier),
            null,
            null,
            $languageKey,
            $alternativeLanguageKeys
        );
    }

    /**
     * Set an override language label
     *
     * @param mixed $identifier
     * @param mixed $value
     * @return void
     */
    public function offsetSet($identifier, $value): void
    {
        $this->overrideLabels[$identifier] = $value;
    }

    /**
     * Remove an override language label
     *
     * @param mixed $identifier
     * @return void
     */
    public function offsetUnset($identifier): void
    {
        unset($this->overrideLabels[$identifier]);
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function generateLabelIdentifier(string $identifier): string
    {
        if (!$this->labelsFile) {
            $this->labelsFile = $this->generateLabelFilePath();
        }
        return sprintf('LLL:%s:%s', $this->labelsFile, $identifier);
    }

    /**
     * @return string
     */
    protected function generateLabelFilePath(): string
    {
        $componentLoader = GeneralUtility::makeInstance(ComponentLoader::class);
        $componentFile = $componentLoader->findComponent($this->componentNamespace);
        $componentName = basename($componentFile, '.html');
        $componentPath = dirname($componentFile);
        return $componentPath . DIRECTORY_SEPARATOR . $componentName . '.labels.xlf';
    }
}
