<?php

namespace SMS\FluidComponents\Tests\Unit;

use SMS\FluidComponents\Utility\ComponentPrefixer\GenericComponentPrefixer;

class GenericComponentPrefixerTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prefixer = new GenericComponentPrefixer();
    }

    /**
     * @test
     */
    public function addTypeAlias()
    {
        $this->assertEquals(
            'vendorAtomMycomponent',
            $this->prefixer->prefix('\VENDOR\MyExtension\Components\Atom\MyComponent')
        );
        $this->assertEquals(
            'vendorAtomMycomponent',
            $this->prefixer->prefix('VENDOR\MyExtension\Components\Atom\MyComponent')
        );
        $this->assertEquals(
            'vendorAtomMycomponent',
            $this->prefixer->prefix(\VENDOR\MyExtension\Components\Atom\MyComponent::class)
        );
    }
}
