<?php

namespace SMS\FluidComponents\Utility;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class ComponentSettings implements \TYPO3\CMS\Core\SingletonInterface, \ArrayAccess
{
    /**
     * Storage of the component settings
     */
    protected array $settings = [];

    public function __construct(protected TypoScriptService $typoScriptService)
    {
        $this->reset();
    }

    /**
     * Resets the settings to the default state (settings from ext_localconf.php and TypoScript)
     */
    public function reset(): void
    {
        $this->settings = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] ?? [],
            $this->typoScriptService->convertTypoScriptArrayToPlainArray(
                $GLOBALS['TSFE']->tmpl->setup['config.']['tx_fluidcomponents.']['settings.'] ?? []
            )
        );
    }

    /**
     * Checks if the specified settings path exists
     */
    public function exists(string $path): bool
    {
        return $this->get($path) !== null;
    }

    /**
     * Returns the value of the specified settings path
     */
    public function get(string $path)
    {
        $path = explode('.', $path);
        $value = $this->settings;
        foreach ($path as $segment) {
            if (!isset($value[$segment])) {
                return;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    /**
     * Sets the value of the specified settings path
     */
    public function set(string $path, mixed $value): self
    {
        $path = explode('.', $path);
        $variable =& $this->settings;
        foreach ($path as $segment) {
            if (!isset($variable[$segment])) {
                $variable[$segment] = [];
            }
            $variable =& $variable[$segment];
        }
        $variable = $value;
        return $this;
    }

    /**
     * Unsets the specified settings path
     */
    public function unset(string $path): self
    {
        $this->set($path, null);
        return $this;
    }

    /**
     * Checks if a subsetting exists; Part of the ArrayAccess implementation
     */
    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->settings[$offset]);
    }

    /**
     * Returns the value of a subsetting; Part of the ArrayAccess implementation
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->settings[$offset];
    }

    /**
     * Sets the value of a subsetting; Part of the ArrayAccess implementation
     */
    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->settings[$offset] = $value;
    }

    /**
     * Unsets a subsetting; Part of the ArrayAccess implementation
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->settings[$offset]);
    }
}
