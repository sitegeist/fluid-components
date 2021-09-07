<?php

declare(strict_types=1);

namespace SMS\FluidComponents;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ServiceProvider extends AbstractServiceProvider
{
    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
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
            $container->get(ObjectManager::class)
        ]);
    }
}
