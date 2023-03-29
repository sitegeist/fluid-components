<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Psr\Container\ContainerInterface;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\Utility\ComponentSettings;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class ComponentRendererFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(): ComponentRenderer
    {
        return new ComponentRenderer(
            $this->container->get(ComponentLoader::class),
            new ComponentSettings(
                new TypoScriptService()
            ),
            new ComponentArgumentConverter(),
            $this->container
        );
    }
}
