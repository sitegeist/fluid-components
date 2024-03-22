<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Service\DataProvider;

use SMS\FluidComponents\Domain\Model\Component;
use SMS\FluidComponents\Interfaces\ComponentDataProvider;

class ComponentPrefixer implements ComponentDataProvider
{
    public function applyData(Component $component): void
    {
        if ($component->getPrefix() !== null) {
            return;
        }

        $vendorName = substr($component->getPackage(), 0, strpos($component->getPackage(), '\\'));
        $componentName = str_replace('\\', '', $component->getName());
        $prefix = strtolower($vendorName) . $componentName;

        $component->setClass($prefix);
        $component->setPrefix($prefix . '_');
    }
}
