<?php declare(strict_types=1);

namespace SMS\FluidComponents\Service;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Imaging\GifBuilder;

class PlaceholderImageService
{
    const STROKE_WIDTH = 18;
    const STROKE_HEIGHT = 3;
    const BACKGROUND_COLOR = '#C0C0C0';
    const COLOR = '#0B0B13';

    public function __construct(
        private readonly GifBuilder $gifBuilder,
        private readonly ?ViewFactoryInterface $viewFactory = null,
    ) {
    }

    public function generate(int $width, int $height, string $format): string
    {
        $text = sprintf('%dx%d.%s', $width, $height, $format);
        if ($format !== 'svg') {
            return $this->generateBitmap($width, $height, $format, $text);
        }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $view = new StandaloneView();
            $view->setTemplatePathAndFilename('EXT:fluid_components/Resources/Private/Templates/Placeholder.svg');
        } else {
            $view = $this->viewFactory->create(new ViewFactoryData(
                templatePathAndFilename: 'EXT:fluid_components/Resources/Private/Templates/Placeholder.svg',
            ));
        }

        $view->assignMultiple([
            'width' => $width,
            'height' => $height,
            'backgroundColor' => self::BACKGROUND_COLOR,
            'color' => self::COLOR,
            'text' => $text,
        ]);
        return 'data:image/svg+xml;base64,' . base64_encode($view->render());
    }

    private function generateBitmap(int $width, int $height, string $format, string $text): string
    {
        $configuration = [
            'XY' => implode(',', [$width, $height]),
                'backColor' => self::BACKGROUND_COLOR,
                'format' => $format,
                '10' => 'TEXT',
                '10.' => [
                    'text' => $text,
                    'fontColor' => self::COLOR,
                    'fontSize' => round($width / 9),
                    'align' => 'center',
                    'offset' => implode(',', [0, $height / 2 + ($width / 9 / 3)]),
                ],
        ];

        $strokes = [
            [0 ,0 , self::STROKE_WIDTH, self::STROKE_HEIGHT],
            [0 ,0 , self::STROKE_HEIGHT, self::STROKE_WIDTH],
            [($width - self::STROKE_WIDTH), 0, $width , self::STROKE_HEIGHT],
            [($width - self::STROKE_HEIGHT), 0, $width,  self::STROKE_WIDTH],
            [0, ($height - self::STROKE_HEIGHT), self::STROKE_WIDTH, $height],
            [0, ($height - self::STROKE_WIDTH), self::STROKE_HEIGHT, $height],
            [($width - self::STROKE_WIDTH), ($height - self::STROKE_HEIGHT), $width, $height],
            [($width - self::STROKE_HEIGHT), ($height - self::STROKE_WIDTH), $width, $height],
        ];

        foreach ($strokes as $key => $dimensions) {
            $configuration[10 * ($key + 2)] = 'BOX';
            $configuration[10 * ($key + 2) . '.'] = [
                'dimensions' => implode(',', $dimensions),
                'color' => self::COLOR,
            ];
        }

        $this->gifBuilder->start($configuration, []);

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13 && is_string($imagePath = $this->gifBuilder->gifBuild())) {
            return $imagePath;
        }
        return (string) $this->gifBuilder->gifBuild()->getPublicUrl();
    }
}
