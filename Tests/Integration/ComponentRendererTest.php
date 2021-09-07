<?php

namespace SMS\FluidComponents\Tests\Integration;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentResolver;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\Utility\ComponentPrefixer\GenericComponentPrefixer;
use SMS\FluidComponents\Utility\ComponentSettings;
use SMS\FluidComponents\ViewHelpers\ComponentViewHelper;
use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use SMS\FluidComponents\ViewHelpers\RendererViewHelper;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\ViewHelpers\Format\DateViewHelper;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;

class ComponentRendererTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    use ProphecyTrait;

    protected $fluidNamespaces;
    protected $componentNamespaces;
    protected $testNamespace = 'SMS\\FluidComponents\\Tests\\Fixtures\\Integration\\Components';
    protected $resetSingletonInstances = true;
    protected $renderingContext;

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

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has(Argument::in([
            ComponentViewHelper::class,
            RendererViewHelper::class,
            ParamViewHelper::class,
            DateViewHelper::class,
            GenericComponentPrefixer::class
        ]))->willReturn(true);
        $containerProphecy->get(Argument::is(ComponentViewHelper::class))->willReturn(new ComponentViewHelper());
        $containerProphecy->get(Argument::is(RendererViewHelper::class))->willReturn(new RendererViewHelper());
        $containerProphecy->get(Argument::is(ParamViewHelper::class))->willReturn(new ParamViewHelper());
        $containerProphecy->get(Argument::is(DateViewHelper::class))->willReturn(new DateViewHelper());
        $containerProphecy->get(Argument::is(GenericComponentPrefixer::class))->willReturn(new GenericComponentPrefixer());

        $objectManagerProphecy = $this->prophesize(ObjectManagerInterface::class);
        $objectManagerProphecy->get(Argument::is(ComponentViewHelper::class))->willReturn(new ComponentViewHelper());
        $objectManagerProphecy->get(Argument::is(RendererViewHelper::class))->willReturn(new RendererViewHelper());
        $objectManagerProphecy->get(Argument::is(ParamViewHelper::class))->willReturn(new ParamViewHelper());
        $objectManagerProphecy->get(Argument::is(DateViewHelper::class))->willReturn(new DateViewHelper());
        $objectManagerProphecy->get(Argument::is(GenericComponentPrefixer::class))->willReturn(new GenericComponentPrefixer());

        $namespaces = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] ?? [];
        $viewHelperResolver = GeneralUtility::makeInstance(
            ComponentResolver::class,
            $containerProphecy->reveal(),
            $objectManagerProphecy->reveal(),
            $namespaces
        );

        $controllerContextProphecy = $this->prophesize(ControllerContext::class);
        $controllerContextProphecy->getRequest(Argument::any())->willReturn(new Request());

        /** @var RenderingContext renderingContext */
        $this->renderingContext = GeneralUtility::makeInstance(
            RenderingContext::class,
            $viewHelperResolver,
            $this->prophesize(FluidCacheInterface::class)->reveal(),
            [],
            []
        );

        $this->renderingContext->setControllerContext($controllerContextProphecy->reveal());

        $containerProphecy->get(Argument::is(RenderingContext::class))->willReturn($this->renderingContext);
        $objectManagerProphecy->get(Argument::is(RenderingContext::class))->willReturn($this->renderingContext);

        $this->invoker = GeneralUtility::makeInstance(ViewHelperInvoker::class);
        $this->renderer = GeneralUtility::makeInstance(
            ComponentRenderer::class,
            new ComponentLoader(),
            new ComponentSettings(new TypoScriptService()),
            new ComponentArgumentConverter(),
            $containerProphecy->reveal()
        );

        $containerProphecy->get(Argument::is(ComponentRenderer::class))->willReturn($this->renderer);
        $containerProphecy->get(Argument::is(ComponentRenderer::class))->willReturn($this->renderer);
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

        $output = $this->invoker->invoke(
            $this->renderer,
            $arguments,
            $this->renderingContext,
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
