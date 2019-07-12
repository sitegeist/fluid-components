<?php

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Resource\FileInterface;

class FalImage extends Image
{
    protected $type = 'FalImage';

    protected $file;

    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }

    public function getFile()
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

    public function getPublicUrl(): string
    {
        return $this->file->getPublicUrl();
    }
}
