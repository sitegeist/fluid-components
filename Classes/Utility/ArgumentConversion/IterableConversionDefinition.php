<?php
declare(strict_types=1);
namespace SMS\FluidComponents\Utility\ArgumentConversion;

use SMS\FluidComponents\Interfaces\ConstructibleFromArray;

class IterableConversionDefinition extends ArgumentConversionDefinition
{
    public string $sourceType;
    public string $interface = ConstructibleFromArray::class;
    public string $factoryMethod = 'fromArray';

    public function __construct(string $sourceType)
    {
        if (!$this->isIterableType($sourceType)) {
            throw new \InvalidArgumentException('Source type must be iterable.', 1679941560);
        }
        $this->sourceType = $sourceType;
    }

    /**
     * Checks if the given type is behaving like an array
     *
     * @param string $typeOrClassName
     * @return boolean
     */
    protected function isIterableType(string $typeOrClassName): bool
    {
        return $typeOrClassName === 'array' ||
            (is_subclass_of($typeOrClassName, \ArrayAccess::class) && is_subclass_of($typeOrClassName, \Traversable::class));
    }
}
