<?php

namespace SMS\FluidComponents\Utility;

use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromExtbaseFile;
use SMS\FluidComponents\Interfaces\ConstructibleFromFileInterface;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;

class ComponentArgumentConverter implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * List of interfaces that provide conversion methods between scalar/compound
     * variable types and complex data structures,
     * e. g. transparently create a link model from a url string
     *
     * @var array
     */
    protected $conversionInterfaces = [
        'string' => [
            ConstructibleFromString::class,
            'fromString'
        ],
        'integer' => [
            ConstructibleFromInteger::class,
            'fromInteger'
        ],
        'array' => [
            ConstructibleFromArray::class,
            'fromArray'
        ],
        FileReference::class => [
            ConstructibleFromFileInterface::class,
            'fromFileInterface'
        ],
        File::class => [
            ConstructibleFromFileInterface::class,
            'fromFileInterface'
        ],
        ProcessedFile::class => [
            ConstructibleFromFileInterface::class,
            'fromFileInterface'
        ],
        \TYPO3\CMS\Extbase\Domain\Model\FileReference::class => [
            ConstructibleFromExtbaseFile::class,
            'fromExtbaseFile'
        ],
        \GeorgRinger\News\Domain\Model\FileReference::class => [
            ConstructibleFromExtbaseFile::class,
            'fromExtbaseFile'
        ],
    ];

    /**
     * Adds an interface to specify argument type conversion to list
     *
     * @param string $fromType
     * @param string $interface
     * @param string $constructor
     * @return self
     */
    public function addConversionInterface(string $fromType, string $interface, string $constructor): self
    {
        $this->conversionInterfaces[$fromType] = [$interface, $constructor];
        return $this;
    }

    /**
     * Removes an interface that specifies argument type conversion from list
     *
     * @param string $fromType
     * @return self
     */
    public function removeConversionInterface(string $fromType): self
    {
        unset($this->conversionInterfaces[$fromType]);
        return $this;
    }

    /**
     * Checks if a given variable type can be converted to another
     * data type by using alternative constructors in $this->conversionInterfaces
     *
     * @param string $givenType
     * @param string $toType
     * @return boolean
     */
    public function canTypeBeConvertedToType(string $givenType, string $toType): bool
    {
        // Check if a constructor interface exists for the given type
        if (!isset($this->conversionInterfaces[$givenType])) {
            return false;
        }

        // Check if the target type is a PHP class
        if (!class_exists($toType)) {
            return false;
        }

        // Check if the target type implements the constructor interface
        // required for conversion
        return is_subclass_of(
            $toType,
            $this->conversionInterfaces[$givenType][0]
        );
    }

    /**
     * Tries to convert the specified value to the specified data type
     * by using alternative constructors in $this->conversionInterfaces
     *
     * @param mixed $value
     * @param string $toType
     * @return mixed
     */
    public function convertValueToType($value, string $toType)
    {
        $givenType = is_object($value) ? get_class($value) : gettype($value);

        // Skip if the type can't be converted
        if (!$this->canTypeBeConvertedToType($givenType, $toType)) {
            return $value;
        }

        // Call alternative constructor provided by interface
        $constructor = $this->conversionInterfaces[$givenType][1];
        return $toType::$constructor($value);
    }
}
