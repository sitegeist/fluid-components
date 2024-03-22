<?php

namespace SMS\FluidComponents\Interfaces;

use SMS\FluidComponents\Domain\Model\Component;

interface ComponentDataProvider
{
    public function applyData(Component $component);
}
