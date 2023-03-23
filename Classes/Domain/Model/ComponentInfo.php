<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * Data Structure to encapsulate component information
 */
class ComponentInfo implements \ArrayAccess
{
    public string $namespace;
    public string $class;
    public string $prefix;
    /**
     * @var array{string, ArgumentDefinition}
     */
    public array $argumentDefinitions;
    /**
     * @var array{string, mixed}
     */
    public array $arguments;

    public function __construct(
        string $namespace,
        string $class,
        string $prefix,
        array $argumentDefinitions,
        array $arguments
    ) {
        $this->namespace = $namespace;
        $this->class = $class;
        $this->prefix = $prefix;
        $this->argumentDefinitions = $argumentDefinitions;
        $this->arguments = $arguments;
    }

    public function offsetExists($offset): bool
    {
        $properties = get_object_vars($this);
        return isset($properties[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $properties = get_object_vars($this);
        return $properties[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \LogicException('ComponentInfo cannot be modified', 1670583538);
    }

    public function offsetUnset($offset): void
    {
        throw new \LogicException('ComponentInfo cannot be modified', 1670583882);
    }
}
