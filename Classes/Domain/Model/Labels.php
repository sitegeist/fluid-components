<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ComponentAware;
use SMS\FluidComponents\Interfaces\ComponentContextAware;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromNull;
use SMS\FluidComponents\Utility\ComponentContext;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Labels implements ComponentAware, ComponentContextAware, \ArrayAccess, ConstructibleFromArray, ConstructibleFromNull
{
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
     * Receive component namespace to determine language file path
     *
     * @param string $componentNamespace
     * @return void
     */
    public function setComponentNamespace(string $componentNamespace): void
    {
        $this->componentNamespace = $componentNamespace;
    }

    /**
     * Receive component context to be able to override language key
     *
     * @param ComponentContext $componentContext
     * @return void
     */
    public function setComponentContext(ComponentContext $componentContext): void
    {
        $this->componentContext = $componentContext;
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

        return LocalizationUtility::translate(
            $this->generateLabelIdentifier($identifier),
            null,
            null,
            $this->componentContext->getLanguageKey(),
            $this->componentContext->getAlternativeLanguageKeys()
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
