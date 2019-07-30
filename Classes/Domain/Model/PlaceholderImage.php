<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Data structure for a placeholder image to be passed to a component
 */
class PlaceholderImage extends Image
{
    /**
     * Type of image to differentiate implementations in Fluid templates
     *
     * @var string
     */
    protected $type = 'PlaceholderImage';

    /**
     * Width of the placeholder image
     *
     * @var integer
     */
    protected $width = 0;

    /**
     * Height of the placeholder image
     *
     * @var integer
     */
    protected $height = 0;

    /**
     * Creates an image object for a placeholder image
     *
     * @param integer $width
     * @param integer $height
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
        return 'https://via.placeholder.com/' . $this->width . 'x' . $this->height;
    }
}
