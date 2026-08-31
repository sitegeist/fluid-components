<?php

declare(strict_types=1);

namespace SMS\FluidComponents;

use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;
use SMS\FluidComponents\Fluid\ViewHelper\ViewHelperResolverFactory;
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
            ViewHelperResolverFactoryInterface::class => static::getViewHelperResolverFactory(...),
        ];
    }

    public static function getViewHelperResolverFactory(ContainerInterface $container): ViewHelperResolverFactory
    {
        return self::new($container, ViewHelperResolverFactory::class, [
            $container,
        ]);
    }
}
