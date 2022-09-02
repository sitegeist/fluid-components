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
        $data[0] = [
            'sourceSimpleField' => 'sourceSimpleValue',
            'sourcePathField' => [
                'path' => 'sourcePathValue'
            ],
            'keepField1' => 'keepValue1',
            'keepField2' => 'keepValue2',
        ];

        yield 'tag content as value' => [
            $data,
            '{dataSource -> fc:variable.map(fieldMapping: {targetSimpleField: "sourceSimpleField", targetPathField: "sourcePathField.path"}, keepFields: "keepField1, keepField2") -> f:variable(name: "dataTarget")}'
                .'{dataTarget.0.targetSimpleField}{dataTarget.0.targetPathField}{dataTarget.0.keepField1}{dataTarget.0.keepField2}',
            $data[0]['sourceSimpleField'] . $data[0]['sourcePathField']['path'] . $data[0]['keepField1'] . $data[0]['keepField2']
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(array $data, string $template, $expected): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->assign('dataSource',$data);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:fc="SMS\FluidComponents\ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);

        self::assertSame($expected, $view->render());
    }
}
