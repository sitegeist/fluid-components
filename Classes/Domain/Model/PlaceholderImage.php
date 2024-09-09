<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Imaging\GifBuilder;

/**
 * Data structure for a placeholder image to be passed to a component
 */
class PlaceholderImage extends Image
{
    /**
     * Type of image to differentiate implementations in Fluid templates
     */
    protected string $type = 'PlaceholderImage';

    /**
     * Width of the placeholder image
     */
    protected int $width = 0;

    /**
     * Height of the placeholder image
     */
    protected int $height = 0;

    /**
     * Creates an image object for a placeholder image
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getPublicUrl(): string
    {
        $gifBuilder = GeneralUtility::makeInstance(GifBuilder::class);
        $gifBuilder->start(
            [
                'XY' => implode(',', [$this->width, $this->height]),
                'backColor' => '#C0C0C0',
                'format' => 'jpg',
                '10' => 'TEXT',
                '10.' => [
                    'text' => implode('x', [$this->width, $this->height]),
                    'fontColor' => '#000000',
                    'fontSize' => 26,
                    'antiAlias' => false,
                    'align' => 'center',
                    'offset' => implode(',', [0,$this->height / 2]),
                ],
            ],
            []
        );

        $prefix = '/';

        if(($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController && $GLOBALS['TSFE']->absRefPrefix !== '') {
            $prefix = $GLOBALS['TSFE']->absRefPrefix;
        }

        return $prefix . $gifBuilder->gifBuild();
    }
}
