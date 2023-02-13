<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Traits\FalFileTrait;
use SMS\FluidComponents\Interfaces\ImageWithCropVariants;
use SMS\FluidComponents\Interfaces\ImageWithDimensions;
use SMS\FluidComponents\Interfaces\ProcessableImage;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Data structure as a wrapper around a FAL object to be passed to a component
 */
class FalImage extends Image implements ImageWithDimensions, ImageWithCropVariants, ProcessableImage
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

    public function process(int $width, int $height, string $format, Area $cropArea): FalImage
    {
        $imageService = GeneralUtility::makeInstance(ImageService::class);
        $processedImage = $imageService->applyProcessingInstructions($this->getFile(), [
            'width' => $width,
            'height' => $height,
            'fileExtension' => $format,
            'crop' => ($cropArea->isEmpty()) ? null : $cropArea->makeAbsoluteBasedOnFile($this->getFile())
        ]);
        return new FalImage($processedImage);
    }
}
