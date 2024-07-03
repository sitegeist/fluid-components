<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Service\DataProvider;

use Psr\Container\ContainerInterface;
use SMS\FluidComponents\Domain\Model\Component;
use SMS\FluidComponents\Interfaces\ComponentDataProvider;
use SMS\FluidComponents\Utility\ComponentPrefixer\ComponentPrefixerInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @deprecated Use ComponentDataProvider instead
 */
class LegacyComponentPrefixer implements ComponentDataProvider, SingletonInterface
{
    private ContainerInterface $container;

    private array $configuration = [];

    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer'])
        ) {
            arsort($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']);
            $this->setConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']);
        }
    }

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function applyData(Component $component): void
    {
        $componentPrefixer = $this->getPrefixerForNamespace($component->getNamespace());
        if (!$componentPrefixer) {
            return;
        }

        $prefix = $componentPrefixer->prefix($component->getNamespace());
        $component->setClass($prefix);
        $component->setPrefix($prefix . $componentPrefixer->getSeparator());
    }

    private function getPrefixerForNamespace(string $namespace): ?ComponentPrefixerInterface
    {
        $componentPrefixer = null;
        foreach ($this->configuration as $targetedNamespace => $className) {
            $targetedNamespace = ltrim($targetedNamespace, '\\');
            if (strpos($namespace, $targetedNamespace) !== 0) {
                continue;
            }

            if ($this->container->has($className)) {
                $componentPrefixer = $this->container->get($className);
            } else {
                $componentPrefixer = GeneralUtility::makeInstance($className);
            }

            if (!($componentPrefixer instanceof ComponentPrefixerInterface)) {
                throw new \TYPO3Fluid\Fluid\Core\ViewHelper\Exception(
                    sprintf('Invalid component prefixer: %s', $className),
                    1530608357
                );
            }
        }
        return $componentPrefixer;
    }

    public function injectContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
