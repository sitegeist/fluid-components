<?php

namespace SMS\FluidComponents\Utility\ComponentPrefixer;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class GenericComponentPrefixer implements ComponentPrefixerInterface
{
    /**
     * Returns the component prefix for the provided component namespaces
     *
     * example:
     *   namespace: \VENDOR\MyExtension\Components\Atom\MyComponent
     *   resulting prefix: vendorAtomMycomponent
     *
     * @param string $namespace
     * @return string
     */
    public function prefix(string $namespace): string
    {
        $namespace = explode('\\', $namespace);
        unset($namespace[1], $namespace[2]);
        return GeneralUtility::underscoredToLowerCamelCase(
            implode('_', $namespace)
        );
    }

    /**
     * Returns the separator to be used between prefix and the following string
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return '_';
    }
}
