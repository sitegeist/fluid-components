<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ImageWithDimensions;
use SMS\FluidComponents\Domain\Model\Traits\FalFileTrait;

/**
 * Data structure as a wrapper around a FAL object to be passed to a component
 */
class FalImage extends Image implements ImageWithDimensions
{
    use FalFileTrait;

    /**
     * Type of image to differentiate implementations in Fluid templates
     *
     * @var string
     */
    protected $type = 'FalImage';


    public function getAlternative(): ?string
    {
        return parent::getAlternative() ?? $this->file->getProperty('alternative');
    }

    public function getCopyright(): ?string
    {
        return parent::getCopyright() ?? $this->file->getProperty('copyright');
    }

    public function getHeight(): int
    {
        return (int) $this->file->getProperty('height');
    }

    public function getWidth(): int
    {
        return (int) $this->file->getProperty('width');
    }
}
