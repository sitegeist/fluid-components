<?php

namespace SMS\FluidComponents\Interfaces;

use SMS\FluidComponents\Utility\ComponentContext;

interface ComponentContextAware
{
    public function setComponentContext(ComponentContext $componentContext): void;
}
