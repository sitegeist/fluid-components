<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional\ViewHelpers\Variable;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class PushViewHelperTest extends FunctionalTestCase
{
    public function renderDataProvider(): \Generator
    {
        $simpleArray = ['a', 'b', 'c', 'd'];
        $arrayWithKeys = ['keyA' => 'a', 'keyB' => 'b', 'keyC' => 'c', 'keyD' => 'd'];

        yield 'simple array' => [
            $simpleArray,
            '<f:variable name="resultArray"></f:variable>'
                . '<f:for each="{inputArray}" as="item">'
                . '<fc:variable.push name="resultArray" item="{item}" />'
                . '</f:for>',
            ['a', 'b', 'c', 'd']
        ];

        yield 'simple array (inline)' => [
            $simpleArray,
            '{f:variable(name: "resultArray")}'
            . '{item -> fc:variable.push(name: "resultArray") -> f:for(each: "{inputArray}", as: "item")}',
            ['a', 'b', 'c', 'd']
        ];

        yield 'array with keys' => [
            $arrayWithKeys,
            '<f:variable name="resultArray"></f:variable>'
            . '<f:for each="{inputArray}" as="item" key="key">'
            . '<fc:variable.push name="resultArray" item="{item}" key="{key}"/>'
            . '</f:for>',
            ['keyA' => 'a', 'keyB' => 'b', 'keyC' => 'c', 'keyD' => 'd']
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(array $input, string $template, array $expected): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->assign('inputArray',$input);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:fc="SMS\FluidComponents\ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);

        $view->render();
        self::assertSame($expected, $view->getRenderingContext()->getVariableProvider()->get('resultArray'));
    }
}
