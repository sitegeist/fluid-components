<?php

declare(strict_types=1);

namespace SMS\FluidComponents;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{
    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
    }

    protected static function getPackageName(): string
    {
        return 'sitegeist/fluid-components';
    }

    public function getFactories(): array
    {
        return [
            \TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface::class => [ static::class, 'getViewHelperResolverFactory' ],
        ];
    }

    public static function getViewHelperResolverFactory(ContainerInterface $container): Fluid\ViewHelper\ViewHelperResolverFactory
    {
        return self::new($container, Fluid\ViewHelper\ViewHelperResolverFactory::class, [
            $container,
        ]);
    }
}
