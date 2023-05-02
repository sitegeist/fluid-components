<?php

namespace SMS\FluidComponents\Domain\Model\Traits;

use SMS\FluidComponents\Domain\Model\File;
use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Data structure as a wrapper around a FAL object to be passed to a component
 */
trait FalFileTrait
{
    /**
     * FAL object
     *
     * @var FileInterface
     */
    protected $file;

    /**
     * Creates an file object as a wrapper around a FAL object
     *
     * @param FileInterface $file
     */
    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }

    /**
     * Creates a file object based on a FAL file uid
     *
     * @param integer $value
     * @return File
     */
    public static function fromInteger(int $value): File
    {
        return static::fromFileUid($value);
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getTitle(): ?string
    {
        return parent::getTitle() ?? $this->file->getProperty('title');
    }

    public function getDescription(): ?string
    {
        return parent::getDescription() ?? $this->file->getProperty('description');
    }

    public function getProperties(): ?array
    {
        return $this->file->getProperties();
    }

    public function getPublicUrl(): string
    {
        return $this->file->getPublicUrl() ?? '';
    }
}
