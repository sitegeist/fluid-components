<?php

declare(strict_types=1);

namespace SMS\FluidComponents;

use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverDelegateRegistry;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentArgumentProcessor;
use SMS\FluidComponents\Fluid\ViewHelper\ViewHelperResolverFactory;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentProcessorInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\StrictArgumentProcessor;

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

    public function getExtensions(): array
    {
        return [
            ArgumentProcessorInterface::class => static::getComponentArgumentProcessor(...),
        ] + parent::getExtensions();
    }

    public static function getViewHelperResolverFactory(ContainerInterface $container): ViewHelperResolverFactory
    {
        return self::new($container, ViewHelperResolverFactory::class, [
            $container,
            $container->get(EventDispatcherInterface::class),
            // Don't provide resolver delegates (including component collections) to InstallTool
            // because it currently doesn't use components and can avoid that additional complexity
            $container->has(ViewHelperResolverDelegateRegistry::class) ? $container->get(ViewHelperResolverDelegateRegistry::class) : null,
            $container->get('fluid.namespaces'),
        ]);
    }

    public static function getComponentArgumentProcessor(
        ContainerInterface $container,
        ?ArgumentProcessorInterface $argumentProcessor = null
    ): ArgumentProcessorInterface {
        return new ComponentArgumentProcessor(
            $argumentProcessor ?? new StrictArgumentProcessor(),
            $container->get(ComponentArgumentConverter::class),
        );
    }
}
