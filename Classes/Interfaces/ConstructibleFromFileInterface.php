<?php

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * ConstructibleFromFileInterface defines an alternative constructor
 * which "converts" the provided File or FileReference to the class implementing
 * the interface
 */
interface ConstructibleFromFileInterface
{
    /**
     * Creates an instance of the class based on the provided implementation
     * of FileInterface
     *
     * @param FileInterface $value
     * @return object
     */
    public static function fromFileInterface(FileInterface $value);
}
