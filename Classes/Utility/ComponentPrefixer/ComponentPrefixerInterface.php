<?php declare(strict_types=1);

namespace SMS\FluidComponents\Utility\ComponentPrefixer;

interface ComponentPrefixerInterface extends \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Returns the component prefix for the provided component namespaces.
     */
    public function prefix(string $namespace): string;

    /**
     * Returns the separator to be used between prefix and the following string.
     */
    public function getSeparator(): string;
}
