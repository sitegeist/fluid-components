<?php

namespace SMS\FluidComponents\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Domain\Model\Link;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;

class LinkTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected ComponentArgumentConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new ComponentArgumentConverter();
    }

    #[Test]
    public function convertStringToLink(): void
    {
        $result = $this->converter->convertValueToType('https://example.com', Link::class);
        $this->assertEquals(new Link('https://example.com'), $result);
    }

    #[Test]
    public function convertEmptyStringToLink(): void
    {
        $result = $this->converter->convertValueToType('', Link::class);
        $this->assertEquals(null, $result);
    }

    public static function linkPropertiesProvider()
    {
        return [
            [
                'https://example.com/path/to/file.ext?param=value',
                'https',
                null,
                null,
                'example.com',
                null,
                '/path/to/file.ext',
                'param=value',
                null
            ],
            [
                'http://user:pass@www.example.com:8000#fragment',
                'http',
                'user',
                'pass',
                'www.example.com',
                8000,
                null,
                null,
                'fragment'
            ]
        ];
    }

    #[Test]
    #[DataProvider('linkPropertiesProvider')]
    public function linkProperties($uri, $scheme, $user, $password, $host, $port, $path, $query, $fragment): void
    {
        $result = $this->converter->convertValueToType($uri, Link::class);
        $this->assertEquals($uri, $result->getUri());
        $this->assertEquals($uri, (string) $result);
        $this->assertEquals($scheme, $result->getScheme());
        $this->assertEquals($user, $result->getUser());
        $this->assertEquals($password, $result->getPass());
        $this->assertEquals($host, $result->getHost());
        $this->assertEquals($port, $result->getPort());
        $this->assertEquals($path, $result->getPath());
        $this->assertEquals($query, $result->getQuery());
        $this->assertEquals($fragment, $result->getFragment());
    }
}
