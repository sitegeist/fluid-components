<?php declare(strict_types=1);

namespace SMS\FluidComponents\Tests\Functional\Service;

use SMS\FluidComponents\Service\PlaceholderImageService;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Imaging\GifBuilder;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class PlaceholderImageServiceTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/fluid_components',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGenerateBitmap(): void
    {
        $gifBuilder = GeneralUtility::makeInstance(GifBuilder::class);
        $service = new PlaceholderImageService($gifBuilder);

        $result = $service->generate(100, 50, 'png');
        $this->assertStringContainsString('100x50.png_', $result);
        $this->assertStringEndsWith('.png', $result);
    }

    public function testGenerateSvg(): void
    {
        $gifBuilder = GeneralUtility::makeInstance(GifBuilder::class);

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $viewFactory = null;
        } else {
            $viewFactory = $this->createMock(\TYPO3\CMS\Core\View\ViewFactoryInterface::class);
            $view = $this->createMock(\TYPO3\CMS\Core\View\ViewInterface::class);
            $view->method('render')->willReturn('<svg></svg>');
            $viewFactory->method('create')->willReturn($view);
            GeneralUtility::addInstance(\TYPO3\CMS\Core\View\ViewFactoryInterface::class, $viewFactory);
        }

        $service = new PlaceholderImageService($gifBuilder, $viewFactory);

        $result = $service->generate(100, 50, 'svg');
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $result);
        $this->assertStringEndsNotWith('data:image/svg+xml;base64,', $result);
    }

}
