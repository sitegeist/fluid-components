<?php

namespace SMS\FluidComponents\Fluid\Rendering;

use SMS\FluidComponents\Fluid\Parser\NodeConverter;

class RenderingContext extends \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
{
    public function __construct()
    {
        parent::__construct();

        // Use custom node converter to be able to use dynamic default values
        // for component params
        $this->getTemplateCompiler()->setNodeConverter(
            new NodeConverter($this->getTemplateCompiler())
        );
    }
}
