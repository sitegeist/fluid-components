<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Exception\InvalidArgumentException;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromFileInterface;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class Image implements ConstructibleFromString, ConstructibleFromInteger, ConstructibleFromArray, ConstructibleFromFileInterface
{
    /**
     * Type of image to differentiate in Fluid templates
     *
     * @var string
     */
    protected $type = 'Image';

    /**
     * Alternative text for the image
     *
     * @var string
     */
    protected $alternative;

    /**
     * Title of the image
     *
     * @var string
     */
    protected $title;

    abstract public function getPublicUrl(): string;

    public static function fromString(string $value)
    {
        return new StaticImage($value);
    }

    public static function fromInteger(int $value)
    {
        return static::fromFileUid($value);
    }

    public static function fromArray(array $value)
    {
        if (isset($value['fileUid'])) {
            $image = static::fromInteger((int) $value);
        } elseif (isset($value['fileReferenceUid'])) {
            $image = static::fromFileReferenceUid((int) $value);
        } elseif (isset($value['fileReference'])) {
            if (!isset($value['tableName']) || !isset($value['fieldName']) || !isset($value['uid'])) {
                throw new InvalidArgumentException(
                    'Invalid file reference description: ' . var_dump($value['fileReference']),
                    1562916587
                );
            }

            $image = static::fromFileReference(
                (string) $value['tableName'],
                (string) $value['fieldName'],
                (int) $value['uid']
            );
        } elseif (isset($value['file'])) {
            $image = static::fromString((string) $value['file']);
        } elseif (isset($value['width']) && isset($value['height'])) {
            $image = static::fromDimensions(
                (int) $value['width'],
                (int) $value['height']
            );
        } else {
            throw new InvalidArgumentException(
                'Invalid set of arguments for conversion to Image instance: ' . var_dump($value),
                1562916607
            );
        }

        if (isset($value['alternative'])) {
            $image->setAlternative($value['alternative']);
        }

        if (isset($value['title'])) {
            $image->setTitle($value['title']);
        }

        return $image;
    }

    public static function fromFileInterface(FileInterface $value)
    {
        return new FalImage($value);
    }

    public static function fromFileUid(int $fileUid)
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $file = $fileRepository->findByUid($fileUid);
        return new FalImage($file);
    }

    public static function fromFileReferenceUid(int $fileReferenceUid)
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReference = $fileRepository->findFileReferenceByUid($fileReferenceUid);
        return new FalImage($fileReference);
    }

    public static function fromFileReference(string $tableName, string $fieldName, int $uid)
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReference = $fileRepository->findByRelation(
            (string) $value['tableName'],
            (string) $value['fieldName'],
            (int) $value['uid']
        );
        return new FalImage($fileReference);
    }

    public static function fromDimensions(int $width, int $height)
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

    public function __toString(): string
    {
        return $this->getPublicUrl();
    }
}
