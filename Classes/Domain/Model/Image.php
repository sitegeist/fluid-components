<?php
declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Exception\FileReferenceNotFoundException;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use SMS\FluidComponents\Exception\InvalidFileArrayException;
use SMS\FluidComponents\Exception\InvalidRemoteImageException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generic data structures to pass images from various sources
 * (FAL, file path, external file uri, placeholder image) to components
 * by using a clear API.
 */
abstract class Image extends File
{
    /**
     * Type of image to differentiate implementations in Fluid templates.
     */
    protected string $type = 'Image';

    /**
     * Alternative text for the image.
     */
    protected ?string $alternative = null;

    /**
     * Copyright of the image.
     */
    protected ?string $copyright = null;

    /**
     * Creates an image object based on a static file (local or remote).
     */
    public static function fromString(string $value): ?self
    {
        if ($value === '') {
            return null;
        }

        try {
            return new RemoteImage($value);
        } catch (InvalidRemoteImageException) {
            $file = GeneralUtility::makeInstance(ResourceFactory::class)->retrieveFileOrFolderObject($value);
            return ($file) ? new FalImage($file) : null;
        }
    }

    /**
     * Creates an image object based on data passed by array. There are numerous
     * array structures that create a valid image object:
     *
     * FAL file object:
     *     [ "fileObject" => $myFileObject ]
     *
     * FAL file uid:
     *
     *     [ "fileUid" => 123 ]
     *
     * FAL file reference uid:
     *
     *     [ "fileReferenceUid" => 456 ]
     *
     * FAL file reference data:
     *
     *     [
     *         "fileReference" => [
     *             "tableName" => "pages",
     *             "fieldName" => "media",
     *             "uid" => 123,
     *             "counter" => 0
     *         ]
     *     ]
     *
     * Static file path:
     *
     *     [ "file" => "EXT:my_extension/Resources/Public/Images/MyImage.png" ]
     *
     *     [
     *         "resource" => [
     *             "extensionName" => "myExtension",
     *             "path" => "Images/MyImage.png"
     *         ]
     *     ]
     *
     *     [
     *         "resource" => [
     *             "extensionKey" => "my_extension",
     *             "path" => "Images/MyImage.png"
     *         ]
     *     ]
     *
     * Static file uri:
     *
     *     [ "file" => "https://example.com/MyImage.png" ]
     *
     * Placeholder image with dimensions:
     *
     *     [ "width" => 1000, "height": 750 ]
     *
     * In addition, each variant can specify values for "alternative" as well as "title":
     *
     *     [
     *         "fileReferenceUid": 456,
     *         "title" => "My Image Title",
     *         "alternative" => "My Alternative Text"
     *     ]
     *
     * @throws FileReferenceNotFoundException|InvalidArgumentException
     */
    public static function fromArray(array $value): ?self
    {
        try {
            /** @var Image */
            $image = parent::fromArray($value);
        } catch (InvalidFileArrayException $e) {
            // Create a placeholder image with the specified dimensions
            if (isset($value['width']) && isset($value['height'])) {
                $image = static::fromDimensions(
                    (int) $value['width'],
                    (int) $value['height']
                );
            } else {
                throw $e;
            }
        }

        if (isset($value['title'])) {
            $image->setTitle($value['title']);
        }

        if (isset($value['description'])) {
            $image->setDescription($value['description']);
        }

        if (isset($value['properties'])) {
            $image->setProperties($value['properties']);
        }

        if (isset($value['alternative'])) {
            $image->setAlternative($value['alternative']);
        }

        if (isset($value['copyright'])) {
            $image->setCopyright($value['copyright']);
        }

        return $image;
    }

    /**
     * Creates a file object as a wrapper around an existing FAL object.
     */
    public static function fromFileInterface(FileInterface $value): self
    {
        return new FalImage($value);
    }

    /**
     * Creates a placeholder image based on the provided image dimensions.
     */
    public static function fromDimensions(int $width, int $height): self
    {
        return new PlaceholderImage($width, $height);
    }

    public function getAlternative(): ?string
    {
        return $this->alternative;
    }

    public function setAlternative(?string $alternative): self
    {
        $this->alternative = $alternative;
        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): self
    {
        $this->copyright = $copyright;
        return $this;
    }
}
