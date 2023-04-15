<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional;

use SMS\FluidComponents\Exception\InvalidArgumentException;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class SlotParameterTest extends FunctionalTestCase
{
    protected $initializeDatabase = false;
    protected $testExtensionsToLoad = [
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
        // Check override order of slot content
        yield 'parameter, named slot and tag content provided' => [
            '<test:contentSlot content="from parameter"><fc:content>from named</fc:content>from children</test:contentSlot>',
            "from parameter\n"
        ];
        yield 'named slot and tag content provided' => [
            '<test:contentSlot><fc:content>from named</fc:content>from children</test:contentSlot>',
            "from named\n"
        ];
        yield 'tag content provided' => [
            '<test:contentSlot>from children</test:contentSlot>',
            "from children\n"
        ];

        // Check if different ways of providing slot content don't interfere between components
        yield 'two component calls with tag content as slot value' => [
            '<test:contentSlot><b>some html</b></test:contentSlot><test:contentSlot><b>other html</b></test:contentSlot>',
            "<b>some html</b>\n<b>other html</b>\n"
        ];
        yield 'two component calls with named slots' => [
            '<test:contentSlot><fc:content><b>some html</b></fc:content></test:contentSlot><test:contentSlot><fc:content><b>other html</b></fc:content></test:contentSlot>',
            "<b>some html</b>\n<b>other html</b>\n"
        ];
        yield 'first component with tag content, second with named slot' => [
            '<test:contentSlot><b>some html</b></test:contentSlot><test:contentSlot><fc:content><b>other html</b></fc:content></test:contentSlot>',
            "<b>some html</b>\n<b>other html</b>\n"
        ];
        yield 'first component with named slot, second with tag content' => [
            '<test:contentSlot><fc:content><b>some html</b></fc:content></test:contentSlot><test:contentSlot><b>other html</b></test:contentSlot>',
            "<b>some html</b>\n<b>other html</b>\n"
        ];

        // Check if slot object behaves correct for if statements
        yield 'unspecified slot parameter' => [
            '<test:slotParameterCheck />',
            "undefined\n"
        ];
        yield 'empty slot parameter' => [
            '<test:slotParameterCheck slot="" />',
            "undefined\n"
        ];
        yield 'specified slot parameter' => [
            '<test:slotParameterCheck slot="content" />',
            "defined\n"
        ];
        yield 'only whitespace as parameter value' => [
            '<test:slotParameterCheck slot=" " />',
            "defined\n"
        ];

        // Check behavior of optional slot parameter
        yield 'provide content to optional slot parameter' => [
            '<test:optionalSlotParameter slot="content" />',
            "content\n"
        ];
        yield 'no error for optional slot parameter' => [
            '<test:optionalSlotParameter />',
            "\n"
        ];

        // Check behavior of multiple slot parameters
        yield 'component with two slots, ordered named slots' => [
            '<test:TwoSlotsAndContent><fc:content slot="slot1">content in slot 1</fc:content><fc:content slot="slot2">content in slot 2</fc:content></test:TwoSlotsAndContent>',
            "content in slot 1|content in slot 2|\n"
        ];
        yield 'component with two slots, unordered named slots' => [
            '<test:TwoSlotsAndContent><fc:content slot="slot2">content in slot 2</fc:content><fc:content slot="slot1">content in slot 1</fc:content></test:TwoSlotsAndContent>',
            "content in slot 1|content in slot 2|\n"
        ];
        yield 'component with two slots, unordered named slots with content slot' => [
            '<test:TwoSlotsAndContent><fc:content slot="slot2">content in slot 2</fc:content><fc:content>some more content</fc:content><fc:content slot="slot1">content in slot 1</fc:content></test:TwoSlotsAndContent>',
            "content in slot 1|content in slot 2|some more content\n"
        ];
        yield 'component with two slots, unordered named slots with tag content' => [
            '<test:TwoSlotsAndContent><fc:content slot="slot2">content in slot 2</fc:content><fc:content slot="slot1">content in slot 1</fc:content>some more content</test:TwoSlotsAndContent>',
            "content in slot 1|content in slot 2|some more content\n"
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, string $expected): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:fc="http://typo3.org/ns/SMS/FluidComponents/ViewHelpers"
            xmlns:test="http://typo3.org/ns/SMS/FluidComponents/Tests/Fixtures/Functional/Components"
            data-namespace-typo3-fluid="true"
            >' . $template);

        // Test without cache
        self::assertSame($expected, $view->render());

        // Test with cache
        self::assertSame($expected, $view->render());
    }

    /**
     * @test
     */
    public function unspecifiedRequiredSlot(): void
    {
        $template = '<test:slotParameter />';

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:fc="http://typo3.org/ns/SMS/FluidComponents/ViewHelpers"
            xmlns:test="http://typo3.org/ns/SMS/FluidComponents/Tests/Fixtures/Functional/Components"
            data-namespace-typo3-fluid="true"
            >' . $template);

        // Test without cache
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1681728555);
        $view->render();

        // Test with cache
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1681728555);
        $view->render();
    }

    /**
     * @test
     */
    public function undefinedSlot(): void
    {
        $template = '<test:slotParameter><fc:content slot="slot">content</fc:content><fc:content slot="invalidSlot">more content</fc:content></test:slotParameter>';

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:fc="http://typo3.org/ns/SMS/FluidComponents/ViewHelpers"
            xmlns:test="http://typo3.org/ns/SMS/FluidComponents/Tests/Fixtures/Functional/Components"
            data-namespace-typo3-fluid="true"
            >' . $template);

        // Test without cache
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1681832624);
        $view->render();

        // Test with cache
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1681832624);
        $view->render();
    }
}
