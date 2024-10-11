<?php

namespace SMS\FluidComponents\Interfaces;

interface ImageWithDimensions
{
    public function getHeight(): int;
    public function getWidth(): int;
}
