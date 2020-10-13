<?php

namespace SMS\FluidComponents\Tests\Unit;

use SMS\FluidComponents\Utility\ComponentLoader;

class ComponentLoaderTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new ComponentLoader();
    }

    protected function getFixturePath($fixtureName)
    {
        return realpath(dirname(__FILE__) . '/../Fixtures/Unit/' . $fixtureName);
    }

    public function addNamespaceProvider()
    {
        return [
            'namespaceWithLeadingTrailingBackslash' => [
                '\\Vendor\\Extension\\Category\\',
                '/path/to/components',
                [
                    'Vendor\\Extension\\Category' => '/path/to/components'
                ]
            ],
            'pathWithTrailingSlash' => [
                'Vendor\\Extension\\Category\\',
                '/path/to/components/',
                [
                    'Vendor\\Extension\\Category' => '/path/to/components'
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider addNamespaceProvider
     */
    public function addNamespace(string $namespace, string $path, array $namespaces)
    {
        $this->loader->addNamespace($namespace, $path);
        $this->assertEquals(
            $namespaces,
            $this->loader->getNamespaces()
        );
    }

    /**
     * @test
     * @depends addNamespace
     */
    public function removeNamespace()
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

    public function setNamespacesProvider()
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
                    '\\Sitegeist\\Fixtures\\Test\\' => '/case1/path8'
                ],
                [
                    'Vendor\\Test\\Namespace' => '/case1/path4',
                    'Sitegeist\\Fixtures\\Test' => '/case1/path8',
                    'Sitegeist\\Fixtures\\AnotherTest' => '/case1/path7',
                    'Sitegeist\\Fixtures\\ComponentLoader\\Test' => '/case1/path3',
                    'Sitegeist\\Fixtures\\ComponentLoader' => '/case1/path6',
                    'Sitegeist\\Fixtures\\Component\\Loader' => '/case1/path2'
                ],
            ]
        ];
    }

    /**
     * @test
     * @dataProvider setNamespacesProvider
     */
    public function setNamespaces(array $namespaces, array $sortedNamespaces)
    {
        $this->loader->setNamespaces($namespaces);
        $this->assertEquals($sortedNamespaces, $this->loader->getNamespaces());
    }

    public function findComponentProvider()
    {
        return [
            'existingComponent' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button',
                '.html',
                $this->getFixturePath('ComponentLoader/Atom/Button/Button.html')
            ],
            'existingComponentFileExtension' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button',
                '.test',
                $this->getFixturePath('ComponentLoader/Atom/Button/Button.test')
            ],
            'existingComponentFirstLevel' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Example',
                '.html',
                $this->getFixturePath('ComponentLoader/Example/Example.html')
            ],
            'existingComponentThirdLevel' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Molecule\\Teaser\\Headline',
                '.html',
                $this->getFixturePath('ComponentLoader/Molecule/Teaser/Headline/Headline.html')
            ],
            'nonexistingComponent' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Label',
                '.html',
                null
            ],
            'nonexistingComponentFileExtension' => [
                'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button',
                '.nonexisting',
                null
            ],
            'nonexistingComponentNamespace' => [
                'Sitegeist\\Fixtures\\Nonexisting\\Component',
                '.html',
                null
            ],
        ];
    }

    /**
     * @test
     * @depends addNamespace
     * @dataProvider findComponentProvider
     */
    public function findComponent(string $componentIdentifier, string $fileExtension, $result)
    {
        $this->loader->addNamespace(
            'Sitegeist\\Fixtures\\ComponentLoader',
            $this->getFixturePath('ComponentLoader')
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

    public function findComponentsInNamespaceProvider()
    {
        return [
            'html' => [
                '.html',
                [
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button' => $this->getFixturePath('ComponentLoader/Atom/Button/Button.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\ComponentLoaderSymlink' => $this->getFixturePath('ComponentLoader/Atom/ComponentLoaderSymlink/ComponentLoaderSymlink.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Link' => $this->getFixturePath('ComponentLoader/Atom/Link/Link.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Example' => $this->getFixturePath('ComponentLoader/Example/Example.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Molecule\\Teaser' => $this->getFixturePath('ComponentLoader/Molecule/Teaser/Teaser.html'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Molecule\\Teaser\\Headline' => $this->getFixturePath('ComponentLoader/Molecule/Teaser/Headline/Headline.html')
                ]
            ],
            'test' => [
                '.test',
                [
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Button' => $this->getFixturePath('ComponentLoader/Atom/Button/Button.test'),
                    'Sitegeist\\Fixtures\\ComponentLoader\\Atom\\Link' => $this->getFixturePath('ComponentLoader/Atom/Link/Link.test')
                ]
            ]
        ];
    }

    /**
     * @test
     * @depends addNamespace
     * @dataProvider findComponentsInNamespaceProvider
     */
    public function findComponentsInNamespace(string $fileExtension, array $result)
    {
        $namespace = 'Sitegeist\\Fixtures\\ComponentLoader';
        $this->loader->addNamespace(
            $namespace,
            $this->getFixturePath('ComponentLoader')
        );

        $this->assertEquals(
            $result,
            $this->loader->findComponentsInNamespace($namespace, $fileExtension)
        );
    }

    /**
     * @depends addNamespace
     * @test
     */
    public function findComponentsInNonexistingNamespace()
    {
        $this->assertEquals(
            [],
            $this->loader->findComponentsInNamespace('Sitegeist\\Fixtures\\NonExistingNamespace', '.html')
        );
    }

    /**
     * @depends addNamespace
     * @test
     */
    public function findComponentsInNonexistingNamespacePath()
    {
        $namespace = 'Sitegeist\\Fixtures\\NonExistingPath';
        $this->loader->addNamespace(
            $namespace,
            $this->getFixturePath('ComponentLoader') . '/NonExisting/'
        );
        $this->assertEquals(
            [],
            $this->loader->findComponentsInNamespace($namespace, '.html')
        );
    }
}
