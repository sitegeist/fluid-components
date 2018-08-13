<?php

namespace SMS\FluidComponents\Fluid\Rendering;

use SMS\FluidComponents\Fluid\Compiler\NodeConverter;
use TYPO3Fluid\Fluid\View\ViewInterface;

class RenderingContext extends \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
{
    public function __construct(ViewInterface $view = null)
    {
        parent::__construct($view);

        // Use custom node converter to be able to use dynamic default values
        // for component params
        $this->getTemplateCompiler()->setNodeConverter(
            new NodeConverter($this->getTemplateCompiler())
        );
    }
}
