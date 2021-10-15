<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Exception\FileReferenceNotFoundException;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use SMS\FluidComponents\Exception\InvalidRemoteImageException;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromExtbaseFile;
use SMS\FluidComponents\Interfaces\ConstructibleFromFileInterface;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generic data structures to pass images from various sources
 * (FAL, file path, external file uri, placeholder image) to components
 * by using a clear API.
 */
abstract class Image implements
    ConstructibleFromString,
    ConstructibleFromInteger,
    ConstructibleFromArray,
    ConstructibleFromFileInterface,
    ConstructibleFromExtbaseFile
{
    /**
     * Type of image to differentiate implementations in Fluid templates
     *
     * @var string
     */
    protected $type = 'Image';

    /**
     * Alternative text for the image
     *
     * @var string|null
     */
    protected $alternative;

    /**
     * Title of the image
     *
     * @var string|null
     */
    protected $title;

    /**
     * Description of the image
     *
     * @var string|null
     */
    protected $description;

    /**
     * Copyright of the image
     *
     * @var string|null
     */
    protected $copyright;

    /**
     * Properties of the image
     *
     * @var array|null
     */
    protected $properties;

    /**
     * Should return the public URL of the image to be used in an img tag
     *
     * @return string
     */
    abstract public function getPublicUrl(): string;

    /**
     * Creates an image object based on a static file (local or remote)
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): ?self
    {
        if ($value === '') {
            return null;
        }

        try {
            return new RemoteImage($value);
        } catch (InvalidRemoteImageException $e) {
            return new LocalImage($value);
        }
    }

    /**
     * Creates an image object based on a FAL file uid
     *
     * @param integer $value
     * @return self
     */
    public static function fromInteger(int $value): self
    {
        return static::fromFileUid($value);
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
     * @param array $value
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $value): self
    {
        // Create an imafe from an existing FAL object
        if (isset($value['fileObject'])) {
            $image = static::fromFileInterface($value['fileObject']);
        // Create an image from file uid
        } elseif (isset($value['fileUid'])) {
            $image = static::fromFileUid((int) $value['fileUid']);
        // Create an image from file reference uid
        } elseif (isset($value['fileReferenceUid'])) {
            $image = static::fromFileReferenceUid((int) $value['fileReferenceUid']);
        // Create an image from file reference data (table, field, uid, counter)
        } elseif (isset($value['fileReference']) && is_array($value['fileReference'])) {
            $fileReference = $value['fileReference'];

            if (!isset($fileReference['tableName'])
                || !isset($fileReference['fieldName'])
                || !isset($fileReference['uid'])
            ) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid file reference description: %s',
                    print_r($fileReference, true)
                ), 1562916587);
            }

            $image = static::fromFileReference(
                (string) $fileReference['tableName'],
                (string) $fileReference['fieldName'],
                (int) $fileReference['uid'],
                (int) ($fileReference['counter'] ?? 0)
            );
        // Create an image from a static resource in an extension (Resources/Public/...)
        } elseif (isset($value['resource']) && is_array($value['resource'])) {
            $resource = $value['resource'];

            if (!isset($resource['path'])) {
                throw new InvalidArgumentException(sprintf(
                    'Missing path for image resource: %s',
                    print_r($resource, true)
                ), 1564492445);
            }

            if (isset($resource['extensionKey'])) {
                $extensionKey = $resource['extensionKey'];
            } elseif (isset($resource['extensionName'])) {
                $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored(
                    $resource['extensionName']
                );
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Missing extension key or extension name for image resource: %s',
                    print_r($resource, true)
                ), 1564492446);
            }

            $image = static::fromExtensionResource($extensionKey, $resource['path']);
        // Create an image from a file path or uri
        } elseif (isset($value['file'])) {
            $image = static::fromString((string) $value['file']);
        // Create a placeholder image with the specified dimensions
        } elseif (isset($value['width']) && isset($value['height'])) {
            $image = static::fromDimensions(
                (int) $value['width'],
                (int) $value['height']
            );
        } else {
            throw new InvalidArgumentException(sprintf(
                'Invalid set of arguments for conversion to Image instance: %s',
                print_r($value, true)
            ), 1562916607);
        }

        if (isset($value['alternative'])) {
            $image->setAlternative($value['alternative']);
        }

        if (isset($value['title'])) {
            $image->setTitle($value['title']);
        }

        if (isset($value['description'])) {
            $image->setDescription($value['description']);
        }

        if (isset($value['copyright'])) {
            $image->setCopyright($value['copyright']);
        }

        if (isset($value['properties'])) {
            $image->setProperties($value['properties']);
        }

        return $image;
    }

    /**
     * Creates an image object as a wrapper around an existing FAL object
     *
     * @param FileInterface $value
     * @return self
     */
    public static function fromFileInterface(FileInterface $value): self
    {
        return new FalImage($value);
    }

    public static function fromExtbaseFile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $value): self
    {
        return new FalImage($value->getOriginalResource());
    }

    /**
     * Creates an image object based on a FAL file uid
     *
     * @param integer $fileUid
     * @return self
     */
    public static function fromFileUid(int $fileUid): self
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $file = $fileRepository->findByUid($fileUid);
        return new FalImage($file);
    }

    /**
     * Creates an image object based on a FAL file reference uid
     *
     * @param integer $fileReferenceUid
     * @return self
     */
    public static function fromFileReferenceUid(int $fileReferenceUid): self
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReference = $fileRepository->findFileReferenceByUid($fileReferenceUid);
        return new FalImage($fileReference);
    }

    /**
     * Creates an image object based on file reference data
     *
     * @param string $tableName  database table where the file is referenced
     * @param string $fieldName  database field name in which the file is referenced
     * @param integer $uid       uid of the database record in which the file is referenced
     * @param integer $counter   zero-based index of the file reference to use
     *                           (in case there are multiple)
     * @return self
     * @throws FileReferenceNotFoundException
     */
    public static function fromFileReference(
        string $tableName,
        string $fieldName,
        int $uid,
        int $counter = 0
    ): self {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReferences = $fileRepository->findByRelation(
            (string) $tableName,
            (string) $fieldName,
            (int) $uid
        );

        if (!isset($fileReferences[$counter])) {
            throw new FileReferenceNotFoundException(sprintf(
                'File reference in %s.%s for uid %d at position %d could not be found.',
                $tableName,
                $fieldName,
                $uid,
                $counter
            ), 1564495695);
        }

        return new FalImage($fileReferences[$counter]);
    }

    /**
     * Creates an image object based on a static resource in an extension
     * (Resources/Public/...)
     *
     * @param string $extensionKey
     * @param string $path
     * @return self
     * @see \TYPO3\CMS\Fluid\ViewHelpers\Uri\ResourceViewHelper
     */
    public static function fromExtensionResource(string $extensionKey, string $path): self
    {
        return static::fromString('EXT:' . $extensionKey . '/Resources/Public/' . $path);
    }

    /**
     * Creates a placeholder image based on the provided image dimensions
     *
     * @param integer $width
     * @param integer $height
     * @return self
     */
    public static function fromDimensions(int $width, int $height): self
    {
        return new PlaceholderImage($width, $height);
    }

    public function getType(): string
    {
        return $this->type;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function setProperties(?array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Use public url of image as string representation of image objects
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getPublicUrl();
    }
}
