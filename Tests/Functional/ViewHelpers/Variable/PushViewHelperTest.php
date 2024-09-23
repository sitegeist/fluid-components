<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional\ViewHelpers\Variable;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class PushViewHelperTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/fluid_components',
    ];

    public static function renderDataProvider(): Generator
    {
        $simpleArray = ['a', 'b', 'c', 'd'];
        $arrayWithKeys = ['keyA' => 'a', 'keyB' => 'b', 'keyC' => 'c', 'keyD' => 'd'];

        yield 'simple array' => [
            $simpleArray,
            '<f:variable name="resultArray"></f:variable>'
                . '<f:for each="{inputArray}" as="item">'
                . '<fc:variable.push name="resultArray" item="{item}" />'
                . '</f:for>',
            ['a', 'b', 'c', 'd'],
        ];

        yield 'simple array (inline)' => [
            $simpleArray,
            '{f:variable(name: "resultArray")}'
            . '{item -> fc:variable.push(name: "resultArray") -> f:for(each: "{inputArray}", as: "item")}',
            ['a', 'b', 'c', 'd'],
        ];

        yield 'array with keys' => [
            $arrayWithKeys,
            '<f:variable name="resultArray"></f:variable>'
            . '<f:for each="{inputArray}" as="item" key="key">'
            . '<fc:variable.push name="resultArray" item="{item}" key="{key}"/>'
            . '</f:for>',
            ['keyA' => 'a', 'keyB' => 'b', 'keyC' => 'c', 'keyD' => 'd'],
        ];
    }

    #[Test]
    #[DataProvider('renderDataProvider')]
    public function render(array $input, string $template, array $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('fc', 'SMS\\FluidComponents\\ViewHelpers');
        $view->assign('inputArray', $input);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
        self::assertSame($expected, $view->getRenderingContext()->getVariableProvider()->get('resultArray'));
    }
}
