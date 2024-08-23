<?php

namespace SMS\FluidComponents\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Domain\Model\Labels;

class LabelsTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = [
            'SMS\FluidComponents\Tests' => realpath(__DIR__ . '/../../../Fixtures/Unit/ComponentLoader')
        ];
    }

    // public function fetchLabelsFromTranslationFile()
    // {
    //     $labels = new Labels();
    //     $labels->setComponentNamespace('SMS\FluidComponents\Tests\Example');
    //     $this->assertEquals('Test value', $labels['testlabel']);
    // }
    #[Test]
    public function overrideLabelsWithConstructor(): void
    {
        $labels = new Labels(['testlabel' => 'Override value']);
        $this->assertEquals('Override value', $labels['testlabel']);
    }

    #[Test]
    public function overrideLabelsWithArrayConstructor(): void
    {
        $labels = Labels::fromArray(['testlabel' => 'Override value']);
        $this->assertEquals('Override value', $labels['testlabel']);
    }

    #[Test]
    public function overrideLabelsWithArrayAccess(): void
    {
        $labels = new Labels;
        $labels['testlabel'] = 'New override value';
        $this->assertEquals('New override value', $labels['testlabel']);
    }

}
