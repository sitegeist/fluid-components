<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional\ViewHelpers\Variable;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MapViewHelperTest extends FunctionalTestCase
{
    public function renderDataProvider(): \Generator
    {
        $input = [
            0 => [
                'sourceSimpleField' => 'sourceSimpleValue',
                'sourcePathField' => [
                    'path' => 'sourcePathValue'
                ],
                'keepField1' => 'keepValue1',
                'keepField2' => 'keepValue2',
            ]
        ];

        yield 'keep only' => [
            $input,
            '{dataSource -> fc:variable.map(keepFields: "keepField1, keepField2") -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'keepField1' => 'keepValue1',
                    'keepField2' => 'keepValue2',
                ]
            ]
        ];

        yield 'full test' => [
            $input,
            '{dataSource -> fc:variable.map(fieldMapping: {targetSimpleField: "sourceSimpleField", targetPathField: "sourcePathField.path"}, keepFields: "keepField1, keepField2") -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'targetSimpleField' => 'sourceSimpleValue',
                    'targetPathField' => 'sourcePathValue',
                    'keepField1' => 'keepValue1',
                    'keepField2' => 'keepValue2',
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(array $input, string $template, array $expected): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->assign('dataSource',$input);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:fc="SMS\FluidComponents\ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);

        $view->render();
        self::assertSame($expected, $view->getRenderingContext()->getVariableProvider()->get('dataTarget'));
    }
}
