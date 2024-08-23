<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Psr\Container\ContainerInterface;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentResolver;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;

final readonly class ViewHelperResolverFactory implements ViewHelperResolverFactoryInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function create(): ComponentResolver
    {
        $namespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? [];
        return new ComponentResolver($this->container, $namespaces);
    }
}
