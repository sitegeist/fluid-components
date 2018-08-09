<?php

namespace SMS\FluidComponents\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

class ComponentSettings implements \TYPO3\CMS\Core\SingletonInterface, \ArrayAccess
{
    /**
     * Storage of the component settings
     *
     * @var array
     */
    protected $settings = [];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Resets the settings to the default state (settings from ext_localconf.php and TypoScript)
     *
     * @return void
     */
    public function reset()
    {
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->settings = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings'] ?? [],
            $typoScriptService->convertTypoScriptArrayToPlainArray(
                $GLOBALS['TSFE']->tmpl->setup['config.']['tx_fluidcomponents.']['settings.'] ?? []
            )
        );
    }

    /**
     * Checks if the specified settings path exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path)
    {
        return $this->get($path) !== null;
    }

    /**
     * Returns the value of the specified settings path
     *
     * @param string $path
     * @return mixed
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
     *
     * @param string $path
     * @param mixed $value
     * @return self
     */
    public function set(string $path, $value)
    {
        $path = explode('.', $path);
        $variable = $this->settings;
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
     *
     * @param string $path
     * @return self
     */
    public function unset(string $path)
    {
        $this->set($path, null);
        return $this;
    }

    /**
     * Checks if a subsetting exists; Part of the ArrayAccess implementation
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->settings[$offset]);
    }

    /**
     * Returns the value of a subsetting; Part of the ArrayAccess implementation
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->settings[$offset];
    }

    /**
     * Sets the value of a subsetting; Part of the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->settings[$offset] = $value;
    }

    /**
     * Unsets a subsetting; Part of the ArrayAccess implementation
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->settings[$offset]);
    }
}
