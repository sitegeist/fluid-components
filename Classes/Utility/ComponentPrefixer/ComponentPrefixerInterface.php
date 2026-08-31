<?php declare(strict_types=1);

namespace SMS\FluidComponents\Utility\ComponentPrefixer;

use TYPO3\CMS\Core\SingletonInterface;

interface ComponentPrefixerInterface extends SingletonInterface
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
