<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional\ViewHelpers\Variable;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class MapViewHelperTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/fluid_components',
    ];

    public static function renderDataProvider(): Generator
    {
        $input = [
            0 => [
                'sourceSimpleField' => 'sourceSimpleValue',
                'sourcePathField' => [
                    'path' => 'sourcePathValue',
                ],
                'keepField1' => 'keepValue1',
                'keepField2' => 'keepValue2',
            ],
        ];

        $obj = new stdClass;
        $obj->sourceSimpleField = 'sourceSimpleValue';
        $obj->sourcePathField = new stdClass;
        $obj->sourcePathField->path = 'sourcePathValue';
        $obj->keepField1 = 'keepValue1';
        $obj->keepField2 = 'keepValue2';

        yield 'array of objects' => [
            [0 => $obj],
            '{dataSource -> fc:variable.map(fieldMapping: {targetSimpleField: "sourceSimpleField", targetPathField: "sourcePathField.path"}, keepFields: "keepField1, keepField2") -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'targetSimpleField' => 'sourceSimpleValue',
                    'targetPathField' => 'sourcePathValue',
                    'keepField1' => 'keepValue1',
                    'keepField2' => 'keepValue2',
                ],
            ],
        ];

        yield 'keep only (coma separated list)' => [
            $input,
            '{dataSource -> fc:variable.map(keepFields: "keepField1, keepField2") -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'keepField1' => 'keepValue1',
                    'keepField2' => 'keepValue2',
                ],
            ],
        ];

        yield 'keep only (array of fields)' => [
            $input,
            '{dataSource -> fc:variable.map(keepFields: "{0: \'keepField1\', 1: \'keepField2\'}") -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'keepField1' => 'keepValue1',
                    'keepField2' => 'keepValue2',
                ],
            ],
        ];

        yield 'simple field' => [
            $input,
            '{dataSource -> fc:variable.map(fieldMapping: {targetSimpleField: "sourceSimpleField"}) -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'targetSimpleField' => 'sourceSimpleValue',
                ],
            ],
        ];

        yield 'simple field (dataSource as subject)' => [
            $input,
            '{fc:variable.map(subject: dataSource, fieldMapping: {targetSimpleField: "sourceSimpleField"}) -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'targetSimpleField' => 'sourceSimpleValue',
                ],
            ],
        ];

        yield 'simple field (tag version)' => [
            $input,
            '<f:variable name="dataTarget"><fc:variable.map fieldMapping="{targetSimpleField: \'sourceSimpleField\'}">{dataSource}</fc:variable.map></f:variable>',
            [
                0 => [
                    'targetSimpleField' => 'sourceSimpleValue',
                ],
            ],
        ];

        yield 'path field' => [
            $input,
            '{dataSource -> fc:variable.map(fieldMapping: {targetPathField: "sourcePathField.path"}) -> f:variable(name: "dataTarget")}',
            [
                0 => [
                    'targetPathField' => 'sourcePathValue',
                ],
            ],
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
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('renderDataProvider')]
    public function render(array $input, string $template, array $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('fc', 'SMS\\FluidComponents\\ViewHelpers');
        $view->assign('dataSource', $input);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
        self::assertSame($expected, $view->getRenderingContext()->getVariableProvider()->get('dataTarget'));
    }
}
