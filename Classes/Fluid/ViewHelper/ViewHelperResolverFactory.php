<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Psr\Container\ContainerInterface;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentResolver;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;

final class ViewHelperResolverFactory implements ViewHelperResolverFactoryInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(): ComponentResolver
    {
        $namespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? [];
        return new ComponentResolver($this->container, $namespaces);
    }
}
