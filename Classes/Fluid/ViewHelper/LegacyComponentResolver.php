<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

class LegacyComponentResolver extends ComponentResolver
{
    /**
     * Uses ExtBase's object manager to inject ComponentRenderer into
     * Fluid viewhelper processing
     *
     * @param string $viewHelperClassName
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface
     */
    public function createViewHelperInstanceFromClassName($viewHelperClassName): ViewHelperInterface
    {
        if (class_exists($viewHelperClassName, true)) {
            return $this->getObjectManager()->get($viewHelperClassName);
        } else {
            // Redirect all components to special ViewHelper ComponentRenderer
            $componentRenderer = $this->getObjectManager()->get(ComponentRenderer::class);

            $componentRenderer->setComponentNamespace($viewHelperClassName);

            return $componentRenderer;
        }
    }

    protected function getComponentLoader(): ComponentLoader
    {
        return GeneralUtility::makeInstance(ComponentLoader::class);
    }
}
