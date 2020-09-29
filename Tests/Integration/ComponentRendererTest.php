<?php

namespace SMS\FluidComponents\Tests\Integration;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;

class ComponentRendererTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected $fluidNamespaces;
    protected $componentNamespaces;

    protected $testNamespace = 'SMS\\FluidComponents\\Tests\\Fixtures\\Integration\\Components';

    protected $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();

        // Add fc ViewHelpers
        $this->fluidNamespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

        // Register test components
        $this->componentNamespaces = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = [
            $this->testNamespace => realpath(dirname(__FILE__) . '/../Fixtures/Integration/Components/')
        ];

        // Register and then disable fluid cache
        // This is necessary because of the way the RenderingContext deals with the fluid cache
        $cacheClass = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['fluid_template']['frontend'];
        $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $cacheManager->registerCache(new $cacheClass('fluid_template', new NullBackend(null)));

        $this->invoker = GeneralUtility::makeInstance(ViewHelperInvoker::class);
        $this->renderer = GeneralUtility::makeInstance(ComponentRenderer::class);
    }

    public function renderComponentProvider()
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
            ['DateTimeParameter', ['date' => 1601371704], '', 'Tue, 29 Sep 2020 09:28:24 +0000']
        ];
    }

    /**
     * @test
     * @dataProvider renderComponentProvider
     */
    public function renderComponent($component, $arguments, $content, $expected)
    {
        // Render single component
        $this->renderer->setComponentNamespace($this->testNamespace . '\\' . $component);
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $output = $this->invoker->invoke(
            $this->renderer,
            $arguments,
            $renderingContext,
            function () use ($content) {
                return $content;
            }
        );

        // Ignore whitespace output from components
        $output = trim($output);

        $this->assertEquals($expected, $output);
    }

    public function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = $this->componentNamespaces;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] = $this->fluidNamespaces;
        parent::tearDown();
    }
}
