<?php

namespace SMS\FluidComponents\Tests\Unit\Utility;

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

    /**
     * @test
     */
    public function addTypeAlias()
    {
        $this->converter->addTypeAlias('Alias', '\Vendor\Original');

        $this->assertEquals(
            '\Vendor\Original',
            $this->converter->resolveTypeAlias('Alias')
        );
    }

    /**
     * @test
     */
    public function removeTypeAlias()
    {
        $this->converter->addTypeAlias('Alias', '\Vendor\Original');
        $this->converter->removeTypeAlias('Alias');

        $this->assertEquals(
            'Alias',
            $this->converter->resolveTypeAlias('Alias')
        );
    }

    /**
     * @test
     */
    public function resolveTypeAliasCollection()
    {
        $this->converter->addTypeAlias('Alias', '\Vendor\Original');

        $this->assertEquals(
            '\Vendor\Original[]',
            $this->converter->resolveTypeAlias('Alias[]')
        );
    }

    /**
     * @test
     */
    public function addRemoveConversionInterface()
    {
        $this->assertEquals(
            false,
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class)
        );

        $this->converter->addConversionInterface(
            'string',
            DummyConversionInterface::class,
            'fromString'
        );

        $this->assertEquals(
            true,
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class)
        );

        $this->converter->removeConversionInterface('string');

        $this->assertEquals(
            false,
            $this->converter->canTypeBeConvertedToType('string', DummyValue::class)
        );
    }

    /**
     * @test
     */
    public function canTypeBeConvertedToType()
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
            [DummyConversionInterface::class, 'fromString'],
            $this->converter->canTypeBeConvertedToType('array', DummyValue::class . '[]')
        );
        $this->assertEquals(
            [DummyConversionInterface::class, 'fromString'],
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

    /**
     * @test
     */
    public function canTypeBeConvertedToTypeCached()
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

    /**
     * @test
     */
    public function convertValueToSameType()
    {
        $this->assertEquals(
            'My string',
            $this->converter->convertValueToType('My string', 'string')
        );
    }

    /**
     * @test
     */
    public function convertValueToUnregisteredType()
    {
        $this->assertEquals(
            'My string',
            $this->converter->convertValueToType('My string', DummyValue::class)
        );
    }

    /**
     * @test
     */
    public function convertValueToType()
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

    /**
     * @test
     */
    public function convertChildClassToType()
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

    /**
     * @test
     */
    public function convertArrayToType()
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

    /**
     * @test
     */
    public function convertIteratorToType()
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
