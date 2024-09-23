<?php declare(strict_types=1);

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ContentViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = true;

    public function initializeArguments(): void
    {
        $this->registerArgument('slot', 'string', 'Slot name', false, ComponentRenderer::DEFAULT_SLOT);
    }

    public function render(): void
    {
    }

    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return '';
    }
}
