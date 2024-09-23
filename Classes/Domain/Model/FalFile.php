<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Traits\FalFileTrait;

/**
 * Data structure as a wrapper around a FAL object to be passed to a component.
 */
class FalFile extends File
{
    use FalFileTrait;

    /**
     * Type of file to differentiate implementations in Fluid templates.
     */
    protected string $type = 'FalFile';
}
