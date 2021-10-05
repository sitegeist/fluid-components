<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Data structure as a wrapper around a FAL object to be passed to a component
 */
class FalImage extends Image
{
    /**
     * Type of image to differentiate implementations in Fluid templates
     *
     * @var string
     */
    protected $type = 'FalImage';

    /**
     * FAL object
     *
     * @var FileInterface
     */
    protected $file;

    /**
     * Creates an image object as a wrapper around a FAL object
     *
     * @param FileInterface $file
     */
    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getAlternative(): ?string
    {
        return parent::getAlternative() ?? $this->file->getProperty('alternative');
    }

    public function getTitle(): ?string
    {
        return parent::getTitle() ?? $this->file->getProperty('title');
    }

    public function getDescription(): ?string
    {
        return parent::getDescription() ?? $this->file->getProperty('description');
    }

    public function getCopyright(): ?string
    {
        return $this->file->getProperty('copyright');
    }

    public function getProperties(): ?array
    {
        return $this->file->getProperties();
    }

    public function getPublicUrl(): string
    {
        return $this->file->getPublicUrl();
    }
}
