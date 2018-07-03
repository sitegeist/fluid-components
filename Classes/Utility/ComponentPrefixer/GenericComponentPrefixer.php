<?php

namespace SMS\FluidComponents\Utility\ComponentPrefixer;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class GenericComponentPrefixer implements ComponentPrefixerInterface
{
    /**
     * Returns the component prefix for the provided component namespaces
     *
     * example:
     *   namespace: \SMS\FluidComponentsExample\Components\MyComponent
     *   resulting prefix: smsFluidcomponentsexampleMycomponent
     *
     * @param string $namespace
     * @return string
     */
    public function prefix(string $namespace): string
    {
        $namespace = explode('\\', $namespace);
        $componentName = end($namespace);
        return GeneralUtility::underscoredToLowerCamelCase(
            implode('_', [$namespace[0], $namespace[1], $componentName])
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
