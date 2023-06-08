<?php
declare(strict_types=1);
namespace SMS\FluidComponents\Domain\Model;

/**
 * @internal
 */
final class RequiredSlotPlaceholder extends Slot
{
    public function __construct()
    {
        parent::__construct('');
    }

    public static function __set_state(array $properties): self
    {
        return new self;
    }
}
