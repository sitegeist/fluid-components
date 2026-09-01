<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentResolver;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverDelegateRegistry;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;
use TYPO3\CMS\Fluid\Event\ModifyNamespacesEvent;

final readonly class ViewHelperResolverFactory implements ViewHelperResolverFactoryInterface
{
    public function __construct(
        private ContainerInterface $container,
        private EventDispatcherInterface $eventDispatcher,
        private ?ViewHelperResolverDelegateRegistry $viewHelperResolverDelegateRegistry,
        private iterable $namespaces,
    ) {
    }

    public function create(): ComponentResolver
    {
        // @deprecated remove merging with TYPO3_CONF_VARS in TYPO3 v15.0
        $namespaces = array_merge_recursive(
            (array)$this->namespaces,
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? [],
        );
        $event = $this->eventDispatcher->dispatch(new ModifyNamespacesEvent($namespaces));
        return new ComponentResolver(
            $this->container,
            $event->getNamespaces(),
            $this->viewHelperResolverDelegateRegistry instanceof ViewHelperResolverDelegateRegistry ? iterator_to_array($this->viewHelperResolverDelegateRegistry->getAll()) : [],
        );
    }
}
