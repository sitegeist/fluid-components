<?php

namespace SMS\FluidComponents\Tests\Unit\DataStructure;

use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Domain\Model\DateTime;

class DateTimeTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new ComponentArgumentConverter();
    }

    public function convertToDateTimeProvider()
    {
        return [
            [new \DateTime('tomorrow'), new DateTime('tomorrow')],
            ['tomorrow', new DateTime('tomorrow')],
            ['2020-01-01 12:34:56', new DateTime('2020-01-01 12:34:56')],
            [1601301635, new DateTime('@1601301635')]
        ];
    }

    /**
     * @test
     * @dataProvider convertToDateTimeProvider
     */
    public function convertToDateTime($value, $expected)
    {
        $result = $this->converter->convertValueToType($value, DateTime::class);
        $this->assertEquals($expected, $result);
    }
}
