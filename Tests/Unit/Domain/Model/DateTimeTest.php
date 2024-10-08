<?php declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Domain\Model\DateTime;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;

class DateTimeTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected ComponentArgumentConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new ComponentArgumentConverter();
    }

    public static function convertToDateTimeProvider()
    {
        return [
            [new \DateTime('tomorrow'), new DateTime('tomorrow')],
            ['tomorrow', new DateTime('tomorrow')],
            ['2020-01-01 12:34:56', new DateTime('2020-01-01 12:34:56')],
            [1601301635, new DateTime('@1601301635')],
        ];
    }

    #[Test]
    #[DataProvider('convertToDateTimeProvider')]
    public function convertToDateTime($value, $expected): void
    {
        $result = $this->converter->convertValueToType($value, DateTime::class);
        $this->assertEquals($expected, $result);
    }
}
