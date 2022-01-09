<?php

namespace SMS\FluidComponents\Tests\Unit\Domain\Model;

use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Domain\Model\Link;

class LinkTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new ComponentArgumentConverter();
    }

    /**
     * @test
     */
    public function convertStringToLink()
    {
        $result = $this->converter->convertValueToType('https://example.com', Link::class);
        $this->assertEquals(new Link('https://example.com'), $result);
    }

    /**
     * @test
     */
    public function convertEmptyStringToLink()
    {
        $result = $this->converter->convertValueToType('', Link::class);
        $this->assertEquals(null, $result);
    }

    public function linkPropertiesProvider()
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

    /**
     * @test
     * @dataProvider linkPropertiesProvider
     */
    public function linkProperties($uri, $scheme, $user, $password, $host, $port, $path, $query, $fragment)
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
