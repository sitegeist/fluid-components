<?php

namespace SMS\FluidComponents\Utility;

use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromDateTime;
use SMS\FluidComponents\Interfaces\ConstructibleFromDateTimeImmutable;
use SMS\FluidComponents\Interfaces\ConstructibleFromExtbaseFile;
use SMS\FluidComponents\Interfaces\ConstructibleFromFileInterface;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromNull;
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
        'NULL' => [
            ConstructibleFromNull::class,
            'fromNull'
        ],
        'DateTime' => [
            ConstructibleFromDateTime::class,
            'fromDateTime'
        ],
        'DateTimeImmutable' => [
            ConstructibleFromDateTimeImmutable::class,
            'fromDateTimeImmutable'
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
     * Registered argument type aliases
     * [alias => full php class name]
     *
     * @var array
     */
    protected $typeAliases = [];

    /**
     * Runtime cache to speed up conversion checks
     *
     * @var array
     */
    protected $conversionCache = [];

    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'])
            && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'])
        ) {
            $this->typeAliases =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'];
        }
    }

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
        $this->conversionCache = [];
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
        $this->conversionCache = [];
        return $this;
    }

    /**
     * Adds an alias for an argument type
     *
     * @param string $alias
     * @param string $type
     * @return self
     */
    public function addTypeAlias(string $alias, string $type): self
    {
        $this->typeAliases[$alias] = $type;
        return $this;
    }

    /**
     * Removes an alias for an argument type
     *
     * @param string $alias
     * @return self
     */
    public function removeTypeAlias(string $alias): self
    {
        unset($this->typeAliases[$alias]);
        return $this;
    }

    /**
     * Replaces potential argument type alias with real php class name
     *
     * @param string $type  e. g. MyAlias
     * @return string       e. g. Vendor\MyExtension\MyRealClass
     */
    public function resolveTypeAlias(string $type): string
    {
        if ($this->isCollectionType($type)) {
            $subtype = $this->extractCollectionItemType($type);
            return $this->resolveTypeAlias($subtype) . '[]';
        } else {
            return $this->typeAliases[$type] ?? $type;
        }
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
        // No need to convert equal types
        if ($givenType === $toType) {
            return false;
        }

        // Has this check already been computed?
        if (isset($this->conversionCache[$givenType . '|' . $toType])) {
            return $this->conversionCache[$givenType . '|' . $toType];
        }

        // Check if a constructor interface exists for the given type
        // Check if the target type is a PHP class
        $canBeConverted = false;
        if (isset($this->conversionInterfaces[$givenType]) && class_exists($toType)) {
            // Check if the target type implements the constructor interface
            // required for conversion
            $canBeConverted = is_subclass_of(
                $toType,
                $this->conversionInterfaces[$givenType][0]
            );
        } elseif ($this->isCollectionType($toType) && $this->isAccessibleArray($givenType)) {
            $canBeConverted = true;
        }

        // Add to runtime cache
        $this->conversionCache[$givenType . '|' . $toType] = $canBeConverted;

        return $canBeConverted;
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

        // Attempt to convert a collection of objects
        if ($this->isCollectionType($toType)) {
            $subtype = $this->extractCollectionItemType($toType);
            foreach ($value as &$item) {
                $item = $this->convertValueToType($item, $subtype);
            }
            return $value;
        }

        // Call alternative constructor provided by interface
        $constructor = $this->conversionInterfaces[$givenType][1];
        return $toType::$constructor($value);
    }

    /**
     * Checks if the provided type describes a collection of values
     *
     * @param string $type
     * @return boolean
     */
    protected function isCollectionType(string $type): bool
    {
        return substr($type, -2) === '[]';
    }

    /**
     * Extracts the type of individual items from a collection type
     *
     * @param string $type  e. g. Vendor\MyExtension\MyClass[]
     * @return string       e. g. Vendor\MyExtension\MyClass
     */
    protected function extractCollectionItemType(string $type): string
    {
        return substr($type, 0, -2);
    }

    /**
     * Checks if the given type is behaving like an array
     *
     * @param string $typeOrClassName
     * @return boolean
     */
    protected function isAccessibleArray(string $typeOrClassName): bool
    {
        return $typeOrClassName === 'array' ||
            (is_subclass_of($typeOrClassName, \ArrayAccess::class) && is_subclass_of($typeOrClassName, \Traversable::class));
    }
}
