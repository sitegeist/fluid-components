<?php declare(strict_types=1);

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * ConstructibleFromExtbaseFile defines an alternative constructor
 * which "converts" the provided extbase FileReference to the class implementing
 * the interface.
 */
interface ConstructibleFromExtbaseFile
{
    /**
     * Creates an instance of the class based on the provided implementation
     * of extbase FileReference.
     */
    public static function fromExtbaseFile(FileReference $value): object;
}
