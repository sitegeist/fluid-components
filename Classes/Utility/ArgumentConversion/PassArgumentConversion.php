<?php
declare(strict_types=1);
namespace SMS\FluidComponents\Utility\ArgumentConversion;

class PassArgumentConversion implements ArgumentConversionInterface
{
    public function convert($value)
    {
        return $value;
    }
}
