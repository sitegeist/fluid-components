<?php

namespace SMS\FluidComponents\Tests\Unit\Utility;

use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter\BaseObject;
use SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter\BaseObjectConversionInterface;
use SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter\DummyConversionInterface;
use SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter\DummyValue;
use SMS\FluidComponents\Tests\Helpers\ComponentArgumentConverter\SpecificObject;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;

class ComponentArgumentConverterTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected ComponentArgumentConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new ComponentArgumentConverter();
    }

    #[Test]
    public function addTypeAlias(): void
    {
        $this->converter->addTypeAlias('Alias', '\Vendor\Original');

        $this->assertEquals(
            '\Vendor\Original',
            $this->converter->resolveTypeAlias('Alias')
        );
    }

    #[Test]
    public function removeTypeAlias(): void
    {
        $this->converter->addTypeAlias('Alias', '\Vendor\Original');
        $this->converter->removeTypeAlias('Alias');

        $this->assertEquals(
            'Alias',
            $this->converter->resolveTypeAlias('Alias')
        );
    }

    #[Test]
    public function resolveTypeAliasCollection(): void
    {
        $this->converter->addTypeAlias('Alias', '\Vendor\Original');

        $this->assertEquals(
            '\Vendor\Original[]',
            $this->converter->resolveTypeAlias('Alias[]')
        );
    }

    #[Test]
    public function addRemoveConversionInterface(): void
    {
        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class),
            'before conversion interface registration'
        );

        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );

        $this->assertEquals(
            [DummyConversionInterface::class, 'fromString'],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class),
            'after conversion interface registration'
        );

        $this->converter->removeConversionInterface('string');

        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class),
            'after conversion interface removal'
        );
    }

    #[Test]
    public function canTypeBeConvertedToType(): void
    {
        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );
        $this->converter->addConversionInterface(
            BaseObject::class,
            BaseObjectConversionInterface::class,
            'fromBaseObject'
        );

        $this->assertEquals(
            [DummyConversionInterface::class, 'fromString'],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class)
        );
        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType(DummyValue::class, 'string')
        );
        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType('array', DummyValue::class)
        );
        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType(DummyValue::class, 'array')
        );

        // No conversion necessary
        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType('string', 'string')
        );

        // Collections
        $this->assertEquals(
            [ConstructibleFromArray::class, 'fromArray'],
            $this->converter->canTypeBeConvertedToType('array', DummyValue::class . '[]')
        );
        $this->assertEquals(
            [ConstructibleFromArray::class, 'fromArray'],
            $this->converter->canTypeBeConvertedToType(\ArrayIterator::class, DummyValue::class . '[]')
        );
        $this->assertEquals(
            [],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class . '[]')
        );

        // Class inheritance
        $this->assertEquals(
            [BaseObjectConversionInterface::class, 'fromBaseObject'],
            $this->converter->canTypeBeConvertedToType(BaseObject::class, DummyValue::class)
        );
        $this->assertEquals(
            [BaseObjectConversionInterface::class, 'fromBaseObject'],
            $this->converter->canTypeBeConvertedToType(SpecificObject::class, DummyValue::class)
        );
    }

    #[Test]
    public function canTypeBeConvertedToTypeCached(): void
    {
        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );

        // Uncached result
        $this->assertEquals(
            [DummyConversionInterface::class, 'fromString'],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class)
        );

        // Cached result
        $this->assertEquals(
            [DummyConversionInterface::class, 'fromString'],
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class)
        );
    }

    #[Test]
    public function convertValueToSameType(): void
    {
        $this->assertEquals(
            'My string',
            $this->converter->convertValueToType('My string', 'string')
        );
    }

    #[Test]
    public function convertValueToUnregisteredType(): void
    {
        $this->assertEquals(
            'My string',
            $this->converter->convertValueToType('My string', DummyValue::class)
        );
    }

    #[Test]
    public function convertValueToType(): void
    {
        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );

        $this->assertEquals(
            'My string',
            $this->converter->convertValueToType('My string', DummyValue::class)->value
        );
    }

    #[Test]
    public function convertChildClassToType(): void
    {
        $this->converter->addConversionInterface(
            BaseObject::class,
            BaseObjectConversionInterface::class,
            'fromBaseObject'
        );

        $this->assertEquals(
            'My value',
            $this->converter->convertValueToType(new BaseObject('My value'), DummyValue::class)->value
        );
        $this->assertEquals(
            'My value',
            $this->converter->convertValueToType(new SpecificObject('My value'), DummyValue::class)->value
        );
    }

    #[Test]
    public function convertArrayToType(): void
    {
        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );

        // Check with native array implementation
        $converted = $this->converter->convertValueToType(
            ['first', 'second'],
            DummyValue::class . '[]'
        );

        $this->assertEquals('first', $converted[0]->value);
        $this->assertEquals('second', $converted[1]->value);
    }

    #[Test]
    public function convertIteratorToType(): void
    {
        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );

        // Check with ArrayAccess implementation
        $converted = $this->converter->convertValueToType(
            new \ArrayIterator(['first', 'second']),
            DummyValue::class . '[]'
        );

        $this->assertEquals('first', $converted[0]->value);
        $this->assertEquals('second', $converted[1]->value);
    }
}
