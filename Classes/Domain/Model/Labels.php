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
    public const OVERRIDE_LANGUAGE_KEY = 'languageKey';
    public const OVERRIDE_LANGUAGE_ALTERNATIVES = 'alternativeLanguageKeys';

    /**
     * Namespace of the current component
     */
    protected string $componentNamespace;

    /**
     * Fluid rendering context
     */
    protected RenderingContextInterface $renderingContext;

    /**
     * Static label values that should override those defined in language files
     */
    protected array $overrideLabels = [];

    /**
     * Cache for component labels file
     */
    protected string $labelsFile;

    public function __construct(array $overrideLabels = [])
    {
        $this->overrideLabels = $overrideLabels;
    }

    /**
     * Generate object based on an array passed to the component
     */
    public static function fromArray(array $overrideLabels): self
    {
        return new self($overrideLabels);
    }

    /**
     * Generate object, even if component parameter is optional and omitted
     */
    public static function fromNull(): self
    {
        return new self;
    }

    /**
     * Receive component context to determine language file path
     */
    public function setComponentNamespace(string $componentNamespace): void
    {
        $this->componentNamespace = $componentNamespace;
    }

    /**
     * Receive current fluid rendering context
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Check if language label is defined
     */
    public function offsetExists(mixed $identifier): bool
    {
        return $this->offsetGet($identifier) !== null;
    }

    /**
     * Return value of language label
     */
    public function offsetGet(mixed $identifier): ?string
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
     */
    public function offsetSet(mixed $identifier, mixed $value): void
    {
        $this->overrideLabels[$identifier] = $value;
    }

    /**
     * Remove an override language label
     */
    public function offsetUnset(mixed $identifier): void
    {
        unset($this->overrideLabels[$identifier]);
    }

    protected function generateLabelIdentifier(string $identifier): string
    {
        if (!$this->labelsFile) {
            $this->labelsFile = $this->generateLabelFilePath();
        }
        return sprintf('LLL:%s:%s', $this->labelsFile, $identifier);
    }

    protected function generateLabelFilePath(): string
    {
        $componentLoader = GeneralUtility::makeInstance(ComponentLoader::class);
        $componentFile = $componentLoader->findComponent($this->componentNamespace);
        $componentName = basename((string) $componentFile, '.html');
        $componentPath = dirname((string) $componentFile);
        return $componentPath . DIRECTORY_SEPARATOR . $componentName . '.labels.xlf';
    }
}
