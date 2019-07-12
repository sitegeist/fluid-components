<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceholderImage extends Image
{
    protected $type = 'PlaceholderImage';

    protected $width;

    protected $height;

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
