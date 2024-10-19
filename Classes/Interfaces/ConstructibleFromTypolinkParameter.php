<?php declare(strict_types=1);

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;

/**
 * ConstructibleFromTypolinkParameter defines an alternative constructor
 * which "converts" the provided ConstructibleFromTypolinkParameter instance
 * to the class implementing the interface.
 */
interface ConstructibleFromTypolinkParameter
{
    /**
     * Creates an instance of the class based on the provided object.
     */
    public static function fromTypolinkParameter(TypolinkParameter $value): object;
}
