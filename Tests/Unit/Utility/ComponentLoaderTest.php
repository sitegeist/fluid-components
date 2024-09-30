<?php declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Unit\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Utility\ComponentLoader;

class ComponentLoaderTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected ComponentLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new ComponentLoader();
    }

    protected static function getFixturePath($fixtureName)
    {
        return realpath(__DIR__ . '/../../Fixtures/Unit/' . $fixtureName);
    }

    public static function addNamespaceProvider()
    {
        return [
            'namespaceWithLeadingTrailingBackslash' => [
                '\\Vendor\\Extension\\Category\\',
                '/path/to/components',
                [
                    'Vendor\\Extension\\Category' => '/path/to/components',
                ],
            ],
            'pathWithTrailingSlash' => [
                'Vendor\\Extension\\Category\\',
                '/path/to/components/',
                [
                    'Vendor\\Extension\\Category' => '/path/to/components',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('addNamespaceProvider')]
    public function addNamespace(string $namespace, string $path, array $namespaces): void
    {
        $this->loader->addNamespace($namespace, $path);
        $this->assertEquals(
            $namespaces,
            $this->loader->getNamespaces()
        );
    }

    #[Depends('addNamespace')]
    #[Test]
    public function removeNamespace(): void
    {
        $namespace = 'Vendor\\Extension\\Category';
        $this->loader
            ->addNamespace($namespace, 'some/path')
            ->removeNamespace($namespace);

        $this->assertEquals(
            [],
            $this->loader->getNamespaces()
        );
    }

    public static function setNamespacesProvider()
    {
        return [
            'case1' => [
                [
                    'Sitegeist\\Fixtures\\ComponentLoader' => '/case1/path1',
                    'Sitegeist\\Fixtures\\Component\\Loader' => '/case1/path2',
                    'Sitegeist\\Fixtures\\ComponentLoader\\Test' => '/case1/path3',
                    'Vendor\\Test\\Namespace' => '/case1/path4',
                    '\\Sitegeist\\Fixtures\\ComponentLoader' => '/case1/path5',
                    '\\Sitegeist\\Fixtures\\ComponentLoader\\' => '/case1/path6',
                    '\\Sitegeist\\Fixtures\\AnotherTest\\' => '/case1/path7',
                    '\\Sitegeist\\Fixtures\\Test\\' => '/case1/path8',
                ],
                [
                    'Vendor\\Test\\Namespace' => '/case1/path4',
                    'Sitegeist\\Fixtures\\Test' => '/case1/path8',
                    'Sitegeist\\Fixtures\\AnotherTest' => '/case1/path7',
                    'Sitegeist\\Fixtures\\ComponentLoader\\Test' => '/case1/path3',
                    'Sitegeist\\Fixtures\\ComponentLoader' => '/case1/path6',
                    'Sitegeist\\Fixtures\\Component\\Loader' => '/case1/path2',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('setNamespacesProvider')]
    public function setNamespaces(array $namespaces, array $sortedNamespaces): void
    {
        $this->loader->setNamespaces($namespaces);
        $this->assertEquals($sortedNamespaces, $this->loader->getNamespaces());
    }

    public static function findComponentProvider()
    {
        return [
            'existingComponent' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button',
                '.html',
                self::getFixturePath('ComponentLoader/Atom/Button/Button.html'),
            ],
            'existingComponentFileExtension' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button',
                '.test',
                self::getFixturePath('ComponentLoader/Atom/Button/Button.test'),
            ],
            'existingComponentFirstLevel' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Example',
                '.html',
                self::getFixturePath('ComponentLoader/Example/Example.html'),
            ],
            'existingComponentThirdLevel' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Molecule\\Teaser\\Headline',
                '.html',
                self::getFixturePath('ComponentLoader/Molecule/Teaser/Headline/Headline.html'),
            ],
            'nonexistingComponent' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Label',
                '.html',
                null,
            ],
            'nonexistingComponentFileExtension' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button',
                '.nonexisting',
                null,
            ],
            'nonexistingComponentNamespace' => [
                'Sitegeist\\Fixtures\\Nonexisting\\Component',
                '.html',
                null,
            ],
        ];
    }

    #[Depends('addNamespace')]
    #[Test]
    #[DataProvider('findComponentProvider')]
    public function findComponent(string $componentIdentifier, string $fileExtension, $result): void
    {
        $this->loader->addNamespace(
            'Sitegeist\\Fixtures\\ComponentLoader',
            self::getFixturePath('ComponentLoader')
        );

        // Test uncached version
        $this->assertEquals(
            $result,
            $this->loader->findComponent($componentIdentifier, $fileExtension)
        );

        // Test cached version
        $this->assertEquals(
            $result,
            $this->loader->findComponent($componentIdentifier, $fileExtension)
        );
    }

    public static function findComponentsInNamespaceProvider()
    {
        return [
            'html' => [
                '.html',
                [
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button' => self::getFixturePath('ComponentLoader/Atom/Button/Button.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\ComponentLoaderSymlink' => self::getFixturePath('ComponentLoader/Atom/ComponentLoaderSymlink/ComponentLoaderSymlink.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Link' => self::getFixturePath('ComponentLoader/Atom/Link/Link.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Example' => self::getFixturePath('ComponentLoader/Example/Example.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Molecule\\Teaser' => self::getFixturePath('ComponentLoader/Molecule/Teaser/Teaser.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Molecule\\Teaser\\Headline' => self::getFixturePath('ComponentLoader/Molecule/Teaser/Headline/Headline.html'),
                ],
            ],
            'test' => [
                '.test',
                [
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button' => self::getFixturePath('ComponentLoader/Atom/Button/Button.test'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Link' => self::getFixturePath('ComponentLoader/Atom/Link/Link.test'),
                ],
            ],
        ];
    }

    #[Depends('addNamespace')]
    #[Test]
    #[DataProvider('findComponentsInNamespaceProvider')]
    public function findComponentsInNamespace(string $fileExtension, array $result): void
    {
        $namespace = 'Sitegeist\\Fixtures\\ComponentLoader';
        $this->loader->addNamespace(
            $namespace,
            self::getFixturePath('ComponentLoader')
        );

        $this->assertEquals(
            $result,
            $this->loader->findComponentsInNamespace($namespace, $fileExtension)
        );
    }

    #[Depends('addNamespace')]
    #[Test]
    public function findComponentsInNonexistingNamespace(): void
    {
        $this->assertEquals(
            [],
            $this->loader->findComponentsInNamespace('Sitegeist\\Fixtures\\NonExistingNamespace', '.html')
        );
    }

    #[Depends('addNamespace')]
    #[Test]
    public function findComponentsInNonexistingNamespacePath(): void
    {
        $namespace = 'Sitegeist\\Fixtures\\NonExistingPath';
        $this->loader->addNamespace(
            $namespace,
            self::getFixturePath('ComponentLoader') . '/NonExisting/'
        );
        $this->assertEquals(
            [],
            $this->loader->findComponentsInNamespace($namespace, '.html')
        );
    }
}
