<?php

namespace SMS\FluidComponents\Fluid;

use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use SMS\FluidComponents\Fluid\ComponentRenderer;

class ViewHelperResolver extends \TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver
{
    /**
     * @param string $viewHelperClassName
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface
     */
    public function createViewHelperInstanceFromClassName($viewHelperClassName)
    {
        if ($this->getObjectManager()->isRegistered($viewHelperClassName)) {
            return $this->getObjectManager()->get($viewHelperClassName);
        } else {
            $componentRenderer = $this->getObjectManager()->get(ComponentRenderer::class);

            $componentRenderer->setComponentNamespace($viewHelperClassName);
            
            return $componentRenderer;
        }
    }

/**
     * Resolves a ViewHelper class name by namespace alias and
     * Fluid-format identity, e.g. "f" and "format.htmlspecialchars".
     *
     * Looks in all PHP namespaces which have been added for the
     * provided alias, starting in the last added PHP namespace. If
     * a ViewHelper class exists in multiple PHP namespaces Fluid
     * will detect and use whichever one was added last.
     *
     * If no ViewHelper class can be detected in any of the added
     * PHP namespaces a Fluid Parser Exception is thrown.
     *
     * @param string $namespaceIdentifier
     * @param string $methodIdentifier
     * @return string|NULL
     * @throws ParserException
     */
    public function resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier)
    {
        if (!isset($this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier])) {
            $resolvedViewHelperClassName = $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier);
            $actualViewHelperClassName = implode('\\', array_map('ucfirst', explode('.', $resolvedViewHelperClassName)));
            if (false === class_exists($actualViewHelperClassName) || $actualViewHelperClassName === false) {
                $resolvedViewHelperClassName = $this->resolveComponentName($namespaceIdentifier, $methodIdentifier);
                $actualViewHelperClassName = implode('\\', array_map('ucfirst', explode('.', $resolvedViewHelperClassName)));

                $componentLoader = $this->getComponentLoader();
                $componentFile = $componentLoader->findComponent($actualViewHelperClassName);
                if (!$componentFile) {
                    throw new ParserException(sprintf(
                        'The ViewHelper "<%s:%s>" could not be resolved.' . chr(10) .
                        'Based on your spelling, the system would load the class "%s", however this class does not exist.',
                        $namespaceIdentifier,
                        $methodIdentifier,
                        $resolvedViewHelperClassName
                    ), 1407060572);
                }
            }
            $this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier] = $actualViewHelperClassName;
        }
        return $this->resolvedViewHelperClassNames[$namespaceIdentifier][$methodIdentifier];
    }

    /**
     * Resolve a viewhelper name.
     *
     * @param string $namespaceIdentifier Namespace identifier for the view helper.
     * @param string $methodIdentifier Method identifier, might be hierarchical like "link.url"
     * @return string The fully qualified class name of the viewhelper
     */
    protected function resolveComponentName($namespaceIdentifier, $methodIdentifier)
    {
        $explodedViewHelperName = explode('.', $methodIdentifier);
        if (count($explodedViewHelperName) > 1) {
            $className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
        } else {
            $className = ucfirst($explodedViewHelperName[0]);
        }

        $componentLoader = $this->getComponentLoader();
        $namespaces = (array) $this->namespaces[$namespaceIdentifier];

        do {
            $name = rtrim(array_pop($namespaces), '\\') . '\\' . $className;
        } while (!$componentLoader->findComponent($name) && count($namespaces));

        return $name;
    }

    /**
     * @return ComponentLoader
     */
    protected function getComponentLoader()
    {
        return GeneralUtility::makeInstance(ComponentLoader::class);
    }
}