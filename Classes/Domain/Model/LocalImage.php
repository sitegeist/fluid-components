<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use SMS\FluidComponents\Interfaces\ImageWithDimensions;
use SMS\FluidComponents\Exception\InvalidFilePathException;
use SMS\FluidComponents\Interfaces\ProcessableImage;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

/**
 * Data structure for a local image resource to be passed to a component
 * @deprecated, use FalImage instead
 */
class LocalImage extends Image implements ImageWithDimensions, ProcessableImage
{
    /**
     * Type of image to differentiate implementations in Fluid templates
     */
    protected string $type = 'LocalImage';

    /**
     * Absolute path to the local file
     */
    protected string $filePath = '';

    /**
     * Image width, will be determined at first access
     *
     * @var integer
     */
    protected $width = 0;

    /**
     * Image height, will be determined at first access
     *
     * @var integer
     */
    protected $height = 0;

    /**
     * Creates an image object for a local image resource
     *
     * @throws InvalidFilePathException
     */
    public function __construct(string $filePath)
    {
        $originalFilePath = $filePath;
        $filePath = GeneralUtility::getFileAbsFileName($filePath);

        if ($filePath === '') {
            throw new InvalidFilePathException(sprintf(
                'The specified file path is invalid or is located outside of the TYPO3 context: %s',
                $originalFilePath
            ), 1562925170);
        }

        if (!file_exists($filePath)) {
            throw new InvalidFilePathException(sprintf(
                'The specified file path does not exist: %s',
                $filePath
            ), 1562925132);
        }

        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getPublicUrl(): string
    {
        return PathUtility::getAbsoluteWebPath($this->filePath);
    }

    public function getHeight(): int
    {
        if (!isset($this->height)) {
            $this->getImageDimensions();
        }
        return $this->height;
    }

    public function getWidth(): int
    {
        if (!isset($this->height)) {
            $this->getImageDimensions();
        }
        return $this->width;
    }

    protected function getImageDimensions(): void
    {
        $graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
        $imageDimensions = $graphicalFunctions->getImageDimensions($this->getFilePath());
        $this->width = (int) $imageDimensions[0];
        $this->height = (int) $imageDimensions[1];
    }

    public function process(int $width, int $height, ?string $format, Area $cropArea): ProcessableImage
    {
        $imageService = GeneralUtility::makeInstance(ImageService::class);
        $file = $imageService->getImage($this->getFilePath(), null, false);
        $processedImage = $imageService->applyProcessingInstructions($file, [
            'width' => $width,
            'height' => $height,
            'fileExtension' => $format,
            'crop' => ($cropArea->isEmpty()) ? null : $cropArea->makeAbsoluteBasedOnFile($file)
        ]);
        return new FalImage($processedImage);
    }
}
