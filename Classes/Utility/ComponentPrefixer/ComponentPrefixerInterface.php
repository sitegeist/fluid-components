<?php

namespace SMS\FluidComponents\Utility\ComponentPrefixer;

/**
 * @deprecated Use ComponentDataProvider instead
 */
interface ComponentPrefixerInterface extends \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Returns the component prefix for the provided component namespaces
     *
     * @param string $namespace
     * @return string
     */
    public function prefix(string $namespace): string;

    /**
     * Returns the separator to be used between prefix and the following string
     *
     * @return string
     */
    public function getSeparator(): string;
}
