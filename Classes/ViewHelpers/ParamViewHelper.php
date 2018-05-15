<?php

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ParamViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Parameter name');
        $this->registerArgument('type', 'string', 'Parameter type');
        $this->registerArgument('optional', 'bool', 'Is parameter optional?', false, false);
        $this->registerArgument('default', 'string', 'Default value', false);
    }

    public function render()
    {
        return $this->renderChildren();
    }
}