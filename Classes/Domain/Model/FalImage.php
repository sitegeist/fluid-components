<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Traits\FalFileTrait;
use SMS\FluidComponents\Interfaces\ImageWithCropVariants;
use SMS\FluidComponents\Interfaces\ImageWithDimensions;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;

/**
 * Data structure as a wrapper around a FAL object to be passed to a component
 */
class FalImage extends Image implements ImageWithDimensions, ImageWithCropVariants
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

    public function getDefaultCrop(): Area
    {
        $cropVariantCollection = CropVariantCollection::create((string)$this->file->getProperty('crop'));
        return $cropVariantCollection->getCropArea();
    }

    public function getCropVariant(string $name): Area
    {
        $cropVariantCollection = CropVariantCollection::create((string)$this->file->getProperty('crop'));
        return $cropVariantCollection->getCropArea($name);
    }
}
