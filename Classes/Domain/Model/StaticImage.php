<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Exception\InvalidFilePathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class StaticImage extends Image
{
    protected $type = 'StaticImage';

    protected $filePath;

    public function __construct(string $filePath)
    {
        $filePath = realpath($filePath);

        if (!file_exists($filePath)) {
            throw new InvalidFilePathException(
                'The specified file path does not exist: ' . $filePath,
                1562925132
            );
        }
        if (!GeneralUtility::isAllowedAbsPath($filePath)) {
            throw new InvalidFilePathException(
                'The specified file path is located outside of the TYPO3 context: ' . $filePath,
                1562925170
            );
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
}
