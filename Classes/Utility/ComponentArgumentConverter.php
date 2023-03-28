<?php

namespace SMS\FluidComponents\Utility;

use ArgumentConversionInterface;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromDateTime;
use SMS\FluidComponents\Interfaces\ConstructibleFromDateTimeImmutable;
use SMS\FluidComponents\Interfaces\ConstructibleFromExtbaseFile;
use SMS\FluidComponents\Interfaces\ConstructibleFromFileInterface;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromNull;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;
use SMS\FluidComponents\Utility\ComponentArgumentConversion;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

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
        FileInterface::class => [
            ConstructibleFromFileInterface::class,
            'fromFileInterface'
        ],
        FileReference::class => [
            ConstructibleFromExtbaseFile::class,
            'fromExtbaseFile'
        ]
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

    public function addConversionDefinition(ArgumentConversionDefinition $definition): self
    {
        $this->c
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

    protected function determineConversionDefinition(string $givenType): ?ConversionDefinition
    {
        // Check parent classes and interfaces for matching conversion definitions
        // e. g. FileInterface when File was given
        if (!isset($this->conversionInterfaces[$givenType]) && class_exists($givenType)) {
            $this->conversionInterfaces[$givenType] = array_reduce(
                array_merge(class_implements($givenType), class_parents($givenType)),
                function (?ConversionDefinition $definition, string $className) {
                    return $definition ?? $this->conversionInterfaces[$className] ?? null;
                }
            );
        }
        return $this->conversionInterfaces[$givenType] ?? null;
    }

    /*
    protected function createCollectionConversion(ConversionDefinition $definition, string $toType): ArgumentConversion
    {
        if ($this->isCollectionType($toType)) {
            return new CollectionArgumentConversion(
                $definition,
                $this->createCollectionConversion($this->extractCollectionItemType($toType))
            );
        } else {
            return new ArgumentConversion($this->determineConversionDefinition($toType), $toType);
        }
    }
    */

    /**
     * Checks if a given variable type can be converted to another
     * data type by using alternative constructors in $this->conversionInterfaces
     *
     * @param string $givenType
     * @param string $toType
     * @return array             information about conversion or empty array
     */
    public function canTypeBeConvertedToType(string $givenType, string $toType): ?ArgumentConversionInterface
    {
        // No need to convert equal types
        if ($givenType === $toType) {
            return new PassArgumentConversion;
        }

        // Has this check already been computed?
        if (isset($this->conversionCache[$givenType . '|' . $toType])) {
            return $this->conversionCache[$givenType . '|' . $toType];
        }

        try {
            // Check if a constructor interface exists for the given type
            $definition = $this->determineConversionDefinition($givenType) ?? new IterableConversionDefinition($givenType);

            // Check if the target type implements the constructor interface
            if ($this->isCollectionType($toType)) {
                list($collectionType, $arrayDepth) = $this->extractCollectionItemType($toType);
                $conversion = new CollectionArgumentConversion(
                    $definition,
                    new ArgumentConversion(
                        $this->determineConversionDefinition($collectionType),
                        $collectionType
                    ),
                    $arrayDepth
                );
                $conversion = $this->createCollectionConversion($definition, $toType);
            } else {
                $conversion = new ArgumentConversion($definition, $toType);
            }
        } catch (\InvalidArgumentException $e) {
            // TODO
            if ($this->strict) {
                throw new \Exception();
            } else {
                return null;
            }
        }

        // Add to runtime cache
        $this->conversionCache[$givenType . '|' . $toType] = $conversion;

        return $conversion;

/*

        $conversionInfo = [];
        if (isset($this->conversionInterfaces[$givenType]) &&
            is_subclass_of($toType, $this->conversionInterfaces[$givenType][0])
        ) {
            $conversionInfo = $this->conversionInterfaces[$givenType];
        } elseif ($this->isCollectionType($toType) && $this->isAccessibleArray($givenType)) {
            $conversionInfo = $this->conversionInterfaces['array'] ?? [];
        }

        if (!isset($conversionInfo[0]) && class_exists($givenType)) {
            $parentClasses = array_merge(class_parents($givenType), class_implements($givenType));
            if (is_array($parentClasses)) {
                foreach ($parentClasses as $className) {
                    $conversionInfo = $this->canTypeBeConvertedToType($className, $toType);
                    if (isset($conversionInfo[1])) {
                        break;
                    }
                }
            }
        }

        return $conversionInfo;
*/
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

        if ($converter = $this->canTypeBeConvertedToType($givenType, $toType)) {
            $converter->convert($value);
        } else {
            return $value;
        }

        /*
        // Skip if the type can't be converted
        $conversionInfo = $this->canTypeBeConvertedToType($givenType, $toType);
        if (!$conversionInfo) {
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
        $constructor = $conversionInfo[1];
        return $toType::$constructor($value);
        */
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
     * as well as the expected depth of the collection
     *
     * @param string $type  e. g. Vendor\MyExtension\MyClass[]
     * @return array       e. g. ['Vendor\MyExtension\MyClass', 1]
     */
    protected function extractCollectionItemType(string $type): array
    {
        $arrayDepth = explode('[]', $type);
        return [
            array_shift($arrayDepth),
            count($arrayDepth)
        ];
    }

    /**
     * Checks if the given type is behaving like an array
     *
     * @param string $typeOrClassName
     * @return boolean
     */
    /*
    protected function isAccessibleArray(string $typeOrClassName): bool
    {
        return $typeOrClassName === 'array' ||
            (is_subclass_of($typeOrClassName, \ArrayAccess::class) && is_subclass_of($typeOrClassName, \Traversable::class));
    }
    */
}
