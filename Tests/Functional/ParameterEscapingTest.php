<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional;

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ParameterEscapingTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/fluid_components'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $componentLoader = GeneralUtility::makeInstance(ComponentLoader::class);
        $componentLoader->addNamespace(
            'SMS\\FluidComponents\\Tests\\Fixtures\\Functional\\Components',
            realpath(dirname(__FILE__) . '/../Fixtures/Functional/Components/')
        );
    }

    public function renderDataProvider(): \Generator
    {
        // {content} in component [escape=true]
        yield [
            '<test:contentParameter><b>some html</b></test:contentParameter>',
            "&lt;b&gt;some html&lt;/b&gt;\n"
        ];
        yield [
            '<test:contentParameter><fc:content><b>some html</b></fc:content></test:contentParameter>',
            "&lt;b&gt;some html&lt;/b&gt;\n"
        ];
        yield [
            '<test:contentParameter content="<b>some html</b>" />',
            "&lt;b&gt;some html&lt;/b&gt;\n"
        ];
        yield [
            '<test:contentParameter>{maliciousVariable}</test:contentParameter>',
            "&amp;lt;script&amp;gt;alert(&amp;#039;This JavaScript should not be executed by the browser&amp;#039;)&amp;lt;/script&amp;gt;\n"
        ];
        yield [
            '<test:contentParameter><fc:content>{maliciousVariable}</fc:content></test:contentParameter>',
            "&amp;lt;script&amp;gt;alert(&amp;#039;This JavaScript should not be executed by the browser&amp;#039;)&amp;lt;/script&amp;gt;\n"
        ];
        yield [
            '<test:contentParameter content="{maliciousVariable}" />',
            "&amp;lt;script&amp;gt;alert(&amp;#039;This JavaScript should not be executed by the browser&amp;#039;)&amp;lt;/script&amp;gt;\n"
        ];
        yield [
            '<test:contentParameter>{safeVariable -> f:format.raw()}</test:contentParameter>',
            "&lt;div&gt;Pre-rendered output without unsafe user input&lt;/div&gt;\n"
        ];
        yield [
            '<test:contentParameter><fc:content>{safeVariable -> f:format.raw()}</fc:content></test:contentParameter>',
            "&lt;div&gt;Pre-rendered output without unsafe user input&lt;/div&gt;\n"
        ];
        yield [
            '<test:contentParameter content="{safeVariable -> f:format.raw()}" />',
            "&lt;div&gt;Pre-rendered output without unsafe user input&lt;/div&gt;\n"
        ];

        // {content -> f:format.raw()} in component [escape=true]
        yield [
            '<test:contentRaw><b>some html</b></test:contentRaw>',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:contentRaw><fc:content><b>some html</b></fc:content></test:contentRaw>',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:contentRaw content="<b>some html</b>" />',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:contentRaw>{maliciousVariable}</test:contentRaw>',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:contentRaw><fc:content>{maliciousVariable}</fc:content></test:contentRaw>',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:contentRaw content="{maliciousVariable}" />',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:contentRaw>{safeVariable -> f:format.raw()}</test:contentRaw>',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];
        yield [
            '<test:contentRaw><fc:content>{safeVariable -> f:format.raw()}</fc:content></test:contentRaw>',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];
        yield [
            '<test:contentRaw content="{safeVariable -> f:format.raw()}" />',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];

        // {fc:slot()} in component [escape=true]
        yield [
            '<test:contentSlot><b>some html</b></test:contentSlot>',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:contentSlot><fc:content><b>some html</b></fc:content></test:contentSlot>',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:contentSlot content="<b>some html</b>" />',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:contentSlot>{maliciousVariable}</test:contentSlot>',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:contentSlot><fc:content>{maliciousVariable}</fc:content></test:contentSlot>',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:contentSlot content="{maliciousVariable}" />',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:contentSlot>{safeVariable -> f:format.raw()}</test:contentSlot>',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];
        yield [
            '<test:contentSlot><fc:content>{safeVariable -> f:format.raw()}</fc:content></test:contentSlot>',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];
        yield [
            '<test:contentSlot content="{safeVariable -> f:format.raw()}" />',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];

        // <fc:slot name="slot" /> in component [escape=true]
        yield [
            '<test:SlotParameter slot="<b>some html</b>" />',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:SlotParameter><fc:content slot="slot"><b>some html</b></fc:content></test:SlotParameter>',
            "<b>some html</b>\n"
        ];
        yield [
            '<test:SlotParameter slot="{maliciousVariable}" />',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:SlotParameter><fc:content slot="slot">{maliciousVariable}</fc:content></test:SlotParameter>',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:SlotParameter slot="{safeVariable -> f:format.raw()}" />',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];
        yield [
            '<test:SlotParameter><fc:content slot="slot">{safeVariable -> f:format.raw()}</fc:content></test:SlotParameter>',
            "<div>Pre-rendered output without unsafe user input</div>\n"
        ];

        // {param} in component [escape=false]
        yield [
            '<test:StringParameter param="<b>some html</b>" />',
            "&lt;b&gt;some html&lt;/b&gt;\n"
        ];
        yield [
            '<test:StringParameter param="{maliciousVariable}" />',
            "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"
        ];
        yield [
            '<test:StringParameter param="{safeVariable -> f:format.raw()}" />',
            "&lt;div&gt;Pre-rendered output without unsafe user input&lt;/div&gt;\n"
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setRequest(
            new Request(
                (new ServerRequest)->withAttribute(
                    'extbase',
                    new ExtbaseRequestParameters
                )
            )
        );
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('fc', 'SMS\\FluidComponents\\ViewHelpers');
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'SMS\\FluidComponents\\Tests\\Fixtures\\Functional\\Components');
        $view->assign(
            'maliciousVariable',
            "<script>alert('This JavaScript should not be executed by the browser')</script>"
        );
        $view->assign(
            'safeVariable',
            '<div>Pre-rendered output without unsafe user input</div>'
        );
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);

        // without caching
        self::assertSame($expected, $view->render());

        // with caching
        self::assertSame($expected, $view->render());
    }
}
