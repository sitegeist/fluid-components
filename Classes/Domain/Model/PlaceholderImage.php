<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ImageWithDimensions;
use SMS\FluidComponents\Interfaces\ProcessableImage;
use SMS\FluidComponents\Service\PlaceholderImageService;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Data structure for a placeholder image to be passed to a component.
 */
class PlaceholderImage extends Image implements ImageWithDimensions, ProcessableImage
{
    /**
     * Type of image to differentiate implementations in Fluid templates.
     */
    protected string $type = 'PlaceholderImage';

    /**
     * Image format of the image
     */
    protected string $format = 'svg';

    /**
     * Creates an image object for a placeholder image.
     */
    public function __construct(/**
     * Width of the placeholder image.
     */
    protected int $width, /**
     * Height of the placeholder image.
     */
    protected int $height, ?string $format = null)
    {
        $this->format = $format ?? $this->format;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getPublicUrl(): string
    {
        return GeneralUtility::makeInstance(PlaceholderImageService::class)->generate(
            $this->width,
            $this->height,
            $this->format,
        );
    }

    public function process(int $width, int $height, ?string $format, Area $cropArea): ProcessableImage
    {
        return new PlaceholderImage(
            (int) round($cropArea->getWidth() * $width),
            (int) round($cropArea->getHeight() * $height),
            $format ?: $this->format
        );
    }
}
