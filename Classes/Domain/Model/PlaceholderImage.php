<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ImageWithDimensions;
use SMS\FluidComponents\Interfaces\ProcessableImage;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

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
     * Width of the placeholder image.
     */
    protected int $width = 0;

    /**
     * Height of the placeholder image.
     */
    protected int $height = 0;

    /**
     * Image format of the image
     */
    protected string $format = 'gif';

    /**
     * Creates an image object for a placeholder image.
     */
    public function __construct(int $width, int $height, string $format = 'gif')
    {
        $this->width = $width;
        $this->height = $height;
        $this->format = $format;
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
        return 'https://via.placeholder.com/' . $this->width . 'x' . $this->height . '.' . $this->format;
    }

    public function process(int $width, int $height, ?string $format, Area $cropArea): ProcessableImage
    {
        return new PlaceholderImage(
            (int) round($cropArea->getWidth() * $width),
            (int) round($cropArea->getHeight() * $height),
            $format
        );
    }
}
