<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Service;

use SMS\FluidComponents\Domain\Model\Component;
use TYPO3\CMS\Core\SingletonInterface;

class ComponentDataLoader implements SingletonInterface
{
    private array $loadedComponents = [];
    private iterable $dataProviders;

    public function __construct(iterable $dataProviders)
    {
        $this->dataProviders = $dataProviders;
    }

    public function reset(): void
    {
        $this->loadedComponents = [];
    }

    public function loadData(Component $component): void
    {
        if (isset($this->loadedComponents[$component->getNamespace()])) {
            return;
        }

        foreach ($this->dataProviders as $dataProvider) {
            $dataProvider->applyData($component);
        }
        $this->loadedComponents[$component->getNamespace()] = true;
    }
}
