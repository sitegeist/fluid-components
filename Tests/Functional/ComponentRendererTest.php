<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;

class ComponentRendererTest extends FunctionalTestCase
{
    protected $fluidNamespaces;
    protected $componentNamespaces;
    protected $testNamespace = 'SMS\\FluidComponents\\Tests\\Fixtures\\Functional\\Components';
    protected $resetSingletonInstances = true;

    protected bool $initializeDatabase = false;
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/fluid_components',
    ];

    public function setUp(): void
    {
        parent::setUp();

        // Add fc ViewHelpers
        $this->fluidNamespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

        // Register test components
        $this->componentNamespaces = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = [
            $this->testNamespace => realpath(__DIR__ . '/../Fixtures/Functional/Components/'),
        ];

        // Register and then disable fluid cache
        // This is necessary because of the way the RenderingContext deals with the fluid cache
        $cacheClass = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['fluid_template']['frontend'];
        $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $cacheManager->registerCache(new $cacheClass('fluid_template', new NullBackend(null)));
    }

    public static function renderComponentProvider()
    {
        return [
            ['WithoutParameters', [], '', 'Just a renderer.'],
            ['ContentParameter', ['content' => 'children'], '', 'children'],
            ['ContentParameter', [], 'children', 'children'],
            ['ScalarParameters', [
                'stringParameter' => 'This is a string',
                'integerParameter' => 123,
                'trueParameter' => true, // strange fluid behavior: will be 1
                'falseParameter' => false, // strange fluid behavior: will be empty string
            ], '', 'This is a string|123|1|'],
            ['DateTimeParameter', ['date' => 1601371704], '', 'Tue, 29 Sep 2020 09:28:24 +0000'],
        ];
    }

    #[Test]
    #[DataProvider('renderComponentProvider')]
    public function renderComponent($component, $arguments, $content, $expected): void
    {
        $container = $this->getContainer();

        /** @var ComponentRenderer $renderer */
        $renderer = $container->get(ComponentRenderer::class);
        // Render single component
        $renderer->setComponentNamespace($this->testNamespace . '\\' . $component);

        /** @var ViewHelperInvoker $invoker */
        $invoker = GeneralUtility::makeInstance(ViewHelperInvoker::class);

        $renderingContext = $container->get(RenderingContextFactory::class)->create(
            [],
            new Request(
                (new ServerRequest)->withAttribute(
                    'extbase',
                    new ExtbaseRequestParameters
                )
            )
        );

        $output = $invoker->invoke(
            $renderer,
            $arguments,
            $renderingContext,
            fn() => $content
        );

        // Ignore whitespace output from components
        $output = trim((string) $output);

        $this->assertEquals($expected, $output);
    }

    public function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = $this->componentNamespaces;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] = $this->fluidNamespaces;
        parent::tearDown();
    }
}
