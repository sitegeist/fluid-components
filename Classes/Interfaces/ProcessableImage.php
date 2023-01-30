<?php

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

interface ProcessableImage
{
    public function process(int $height, int $width, string $format, Area $cropArea): ProcessableImage;
}
