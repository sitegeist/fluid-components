<?php

declare(strict_types=1);

namespace SMS\FluidComponents;

use Psr\Container\ContainerInterface;
use SMS\FluidComponents\Command\GenerateXsdCommand;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRendererFactory;
use SMS\FluidComponents\Fluid\ViewHelper\ViewHelperResolverFactory;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\Utility\ComponentSettings;
use TYPO3\CMS\Core\Console\CommandRegistry;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;

class ServiceProvider extends AbstractServiceProvider
{
    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
    }

    public function getFactories(): array
    {
        return [
            ViewHelperResolverFactoryInterface::class => [ static::class, 'getViewHelperResolverFactory' ],
            ComponentRendererFactory::class => [ static::class, 'getComponentRendererFactory' ],
            ComponentLoader::class => [ static::class, 'getComponentLoader' ],
            ComponentRenderer::class => [ static::class, 'getComponentRenderer' ],
            GenerateXsdCommand::class => [ static::class, 'getGenerateXsdCommand' ],
        ];
    }

    public function getExtensions(): array
    {
        return [
                CommandRegistry::class => [ static::class, 'configureCommands' ],
            ] + parent::getExtensions();
    }

    public static function getViewHelperResolverFactory(ContainerInterface $container): ViewHelperResolverFactoryInterface
    {
        return self::new(
            $container,
            ViewHelperResolverFactory::class,
            [
                $container,
                $container->get(ObjectManager::class)
            ]
        );
    }

    public static function getComponentRendererFactory(ContainerInterface $container): ComponentRendererFactory
    {
        return self::new(
            $container,
            ComponentRendererFactory::class,
            [
                $container,
            ]
        );
    }

    public static function getComponentLoader(ContainerInterface $container): ComponentLoader
    {
        return self::new(
            $container,
            ComponentLoader::class
        );
    }

    public static function getComponentRenderer(ContainerInterface $container): ComponentRenderer
    {
        return $container->get(ComponentRendererFactory::class)->create();
    }

    public static function getGenerateXsdCommand(ContainerInterface $container): GenerateXsdCommand
    {
        return self::new(
            $container,
            GenerateXsdCommand::class,
            [
                 'fluidcomponents:generatexsd',
            ]
        );
    }

    public static function configureCommands(ContainerInterface $container, CommandRegistry $commandRegistry): CommandRegistry
    {
        $commandRegistry->addLazyCommand(
            'fluidcomponents:generatexsd',
            GenerateXsdCommand::class,
            'Generates the XSD files for autocompletion in the IDE.'
        );
        return $commandRegistry;
    }
}
