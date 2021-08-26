<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

class ViewHelperResolverFactory extends \TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactory
{
    public function create(): ViewHelperResolver
    {
        $namespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? [];
        return new ViewHelperResolver($this->container, $this->objectManager, $namespaces);
    }
}
