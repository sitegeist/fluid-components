<?php

namespace Spiegel\Sitepackage\ContentObject;

use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use SMS\FluidComponents\Fluid\Rendering\RenderingContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;

class FluidComponentContentObject extends \TYPO3\CMS\Frontend\ContentObject\AbstractContentObject
{
    public function render($conf = [])
    {
        list($namespace, $name) = explode(':', $conf['component'], 2);

        return $this->renderComponent(
            $namespace,
            $name,
            $conf['arguments.'],
            $conf['content'] // TODO cObj
        );
    }

    public function renderComponent($namespace, $name, $arguments, $content)
    {
        /*
        // Check if all required arguments were supplied to the component
        foreach ($component->getArguments() as $expectedArgument) {
            if ($expectedArgument->isRequired() && !isset($data[$expectedArgument->getName()])) {
                throw new RequiredComponentArgumentException(sprintf(
                    'Required argument "%s" was not supplied for component %s.',
                    $expectedArgument->getName(),
                    $component->getName()->getIdentifier()
                ), 1566636254);
            }
        }
        */

        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $identifier = $renderingContext->getViewhelperResolver()->resolveViewHelperClassName(
            $namespace,
            $name
        );

        return ComponentRenderer::renderComponent(
            $arguments,
            function () use ($content) {
                return '';
            },
            $renderingContext,
            $identifier
        );
    }
}
