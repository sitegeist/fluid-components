<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

final class ViewHelperResolverFactory implements \TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface
{
    private ContainerInterface $container;
    private ObjectManagerInterface $objectManager;

    public function __construct(
        ContainerInterface $container,
        ObjectManagerInterface $objectManager
    ) {
        $this->container = $container;
        $this->objectManager = $objectManager;
    }

    public function create(): ComponentResolver
    {
        $namespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? [];
        return new ComponentResolver($this->container, $this->objectManager, $namespaces);
    }
}
