<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Interfaces;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

interface ImageWithCropVariants
{
    public function getDefaultCrop(): Area;
    public function getCropVariant(string $name): Area;
}
