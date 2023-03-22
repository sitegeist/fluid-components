<?php

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

interface ProcessableImage
{
    public function process(int $width, int $height, ?string $format, Area $cropArea): ProcessableImage;
}
