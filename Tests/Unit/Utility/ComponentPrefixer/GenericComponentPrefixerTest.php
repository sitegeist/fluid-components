<?php

namespace SMS\FluidComponents\Tests\Unit\Utility\ComponentPrefixer;

use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Utility\ComponentPrefixer\GenericComponentPrefixer;

class GenericComponentPrefixerTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected GenericComponentPrefixer $prefixer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prefixer = new GenericComponentPrefixer();
    }

    #[Test]
    public function addTypeAlias(): void
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
