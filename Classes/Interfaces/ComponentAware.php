<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * By implementing ComponentAware in a data structure, fluid
 * components will provide the component namespace to the data
 * structure when used in a component call so that the data structure
 * can behave differently for each component (e. g. by reading
 * something from the component's directory)
 */
interface ComponentAware
{
    public function setComponentNamespace(string $componentNamespace): void;
}
