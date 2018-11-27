<?php

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

class ParamViewHelper extends AbstractViewHelper
{
    use ParserRuntimeOnly;

    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Parameter name', true);
        $this->registerArgument('type', 'string', 'Parameter type', true);
        $this->registerArgument('optional', 'bool', 'Is parameter optional?', false, false);
        $this->registerArgument('default', 'string', 'Default value');
        $this->registerArgument('schema', 'string', 'JSON schema for parameter validation, either inline or reference to a file');
        $this->registerArgument('description', 'string', 'Description of the parameter');
    }
}
