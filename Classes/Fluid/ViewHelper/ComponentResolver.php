<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\DependencyInjection\FailsafeContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

class ComponentResolver extends AbstractComponentResolver
{
    /**
     * Uses Symfony dependency injection to inject ComponentRenderer into
     * Fluid viewhelper processing
     *
     * @param string $viewHelperClassName
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface
     */
    public function createViewHelperInstanceFromClassName($viewHelperClassName): ViewHelperInterface
    {
        $classExists = class_exists($viewHelperClassName);
        if ($this->container instanceof FailsafeContainer && $classExists) {
            // The install tool creates VH instances using makeInstance to not rely on symfony DI here,
            // otherwise we'd have to have all install-tool used ones in ServiceProvider.php. However,
            // none of the install tool used VH's use injection.
            /** @var ViewHelperInterface $viewHelperInstance */
            $viewHelperInstance = GeneralUtility::makeInstance($viewHelperClassName);
            return $viewHelperInstance;
        }

        if ($this->container->has($viewHelperClassName)) {
            /** @var ViewHelperInterface $viewHelperInstance */
            $viewHelperInstance = $this->container->get($viewHelperClassName);
            return $viewHelperInstance;
        }

        if ($classExists) {
            /** @var ViewHelperInterface $viewHelperInstance */
            // @deprecated since v11, will be removed with 12. Fallback if extensions VH has no Services.yaml, yet.
            $viewHelperInstance = $this->objectManager->get($viewHelperClassName);
            return $viewHelperInstance;
        }

        // Redirect all components to special ViewHelper ComponentRenderer
        $componentRenderer = $this->container->get(ComponentRendererFactory::class)->create();
        $componentRenderer->setComponentNamespace($viewHelperClassName);

        return $componentRenderer;
    }

    protected function getComponentLoader(): ComponentLoader
    {
        return $this->container->get(ComponentLoader::class);
    }
}
