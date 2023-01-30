<?php

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

interface ImageWithCropVariants
{
    public function getDefaultCrop(): Area;
    public function getCropVariant(string $name): Area;
}
