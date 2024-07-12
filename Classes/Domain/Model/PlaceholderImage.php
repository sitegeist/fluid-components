<?php

namespace SMS\FluidComponents\Domain\Model;

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
        return 'https://via.placeholder.com/' . $this->width . 'x' . $this->height;
    }
}
