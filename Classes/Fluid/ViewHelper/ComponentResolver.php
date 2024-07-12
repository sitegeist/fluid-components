<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DependencyInjection\FailsafeContainer;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

class ComponentResolver extends ViewHelperResolver
{
    /**
     * ViewHelperResolver constructor
     *
     * Loads namespaces defined in global TYPO3 configuration. Overlays `f:`
     * with `f:debug:` when Fluid debugging is enabled in the admin panel,
     * causing debugging-specific ViewHelpers to be resolved in that case.
     *
     * @internal constructor, use `ViewHelperResolverFactory->create()` instead
     */
    public function __construct(ContainerInterface $container, array $namespaces)
    {
        $this->container = $container;
        $this->namespaces = $namespaces;
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
            && $this->getBackendUser() instanceof BackendUserAuthentication
        ) {
            if ($this->getBackendUser()->uc['AdminPanel']['preview_showFluidDebug'] ?? false) {
                $this->namespaces['f'][] = 'TYPO3\\CMS\\Fluid\\ViewHelpers\\Debug';
            }
        }
    }

    /**
     * Uses Symfony dependency injection to inject ComponentRenderer into
     * Fluid viewhelper processing
     *
     * @param string $viewHelperClassName
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface
     */
    public function createViewHelperInstanceFromClassName($viewHelperClassName): ViewHelperInterface
    {
        if ($this->container instanceof FailsafeContainer) {
            // The install tool creates VH instances using makeInstance to not rely on symfony DI here,
            // otherwise we'd have to have all install-tool used ones in ServiceProvider.php. However,
            // none of the install tool used VH's use injection.
            /** @var ViewHelperInterface $viewHelperInstance */
            $viewHelperInstance = GeneralUtility::makeInstance($viewHelperClassName);
            return $viewHelperInstance;
        }

        if (class_exists($viewHelperClassName)) {
            if ($this->container->has($viewHelperClassName)) {
                /** @var ViewHelperInterface $viewHelperInstance */
                $viewHelperInstance = $this->container->get($viewHelperClassName);
            } else {
                /** @var ViewHelperInterface $viewHelperInstance */
                $viewHelperInstance = new $viewHelperClassName;
            }
            return $viewHelperInstance;
        } else {
            // Redirect all components to special ViewHelper ComponentRenderer
            $componentRenderer = $this->container->get(ComponentRenderer::class);
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
            $actualViewHelperClassName = $this->generateViewHelperClassName($resolvedViewHelperClassName);
            if (false === class_exists($actualViewHelperClassName) || $actualViewHelperClassName === false) {
                $resolvedViewHelperClassName = $this->resolveComponentName($namespaceIdentifier, $methodIdentifier);
                $actualViewHelperClassName = $this->generateViewHelperClassName($resolvedViewHelperClassName);

                $componentLoader = $this->getComponentLoader();
                $componentFile = $componentLoader->findComponent($actualViewHelperClassName);
                if (!$componentFile) {
                    throw new ParserException(sprintf(
                        'The ViewHelper "<%s:%s>" could not be resolved.' . chr(10) .
                            'Based on your spelling, the system would load the class "%s", '
                            . 'however this class does not exist.',
                        $namespaceIdentifier,
                        $methodIdentifier,
                        $resolvedViewHelperClassName
                    ), 1527779401);
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
     * Generates a valid PHP class name from the resolved viewhelper class
     *
     * @param string $resolvedViewHelperClassName
     * @return void
     */
    protected function generateViewHelperClassName($resolvedViewHelperClassName)
    {
        return implode('\\', array_map('ucfirst', explode('.', $resolvedViewHelperClassName)));
    }

    protected function getComponentLoader(): ComponentLoader
    {
        return $this->container->get(ComponentLoader::class);
    }
}
