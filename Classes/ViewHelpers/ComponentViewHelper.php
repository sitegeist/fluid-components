<?php

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;

use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use SMS\FluidComponents\ViewHelpers\RendererViewHelper;

class ComponentViewHelper extends AbstractViewHelper
{
    public static $componentData = [];

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Component name');
    }

    /**
     * @return null
     */
    public function render()
    {
        return null;
    }
}