<?php

namespace SMS\FluidComponents\Fluid;

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;

class ComponentView extends AbstractTemplateView
{
    protected ParsedTemplateInterface $parsedTemplate;

    public function __construct(
        ParsedTemplateInterface   $parsedTemplate,
        RenderingContextInterface $context,
    )
    {
        parent::__construct($context);
        $this->parsedTemplate = $parsedTemplate;
    }

    /**
     * @return ParsedTemplateInterface
     */
    protected function getCurrentParsedTemplate()
    {
        return $this->parsedTemplate;
    }
}
