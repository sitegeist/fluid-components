<?php

namespace SMS\FluidComponents\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MapViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('fieldsMapping', 'array', 'Map of fields keys.');
        $this->registerArgument('keepFields', 'mixed', 'Array or comma separated list of fields to keep in array.');
        $this->registerArgument('subject', 'mixed', 'The array of Object to remap.');
    }

    /**
     * @param mixed $subject
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array remapped array
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $subject = $arguments['subject'] ?? $renderChildrenClosure();
        $mapKeyArray = $arguments['fieldsMapping'] ?? [];
        $keepFields = $arguments['keepFields'] ?? [];
        if (false === is_array($keepFields)) {
            $keepFields = array_map('trim', explode(',', $arguments['keepFields']));
        }

        $newArray = [];
        foreach ($subject as $item) {
            $newItem = [];

            foreach ($mapKeyArray as $replace => $key) {
                $newItem[$replace] = VariableExtractor::extract($item, $key);
            }

            //Add another fields from keepFields list
            foreach ($keepFields as $key) {
                $newItem[$key] = VariableExtractor::extract($item, $key);
            }

            $newArray[] = $newItem;
        }

        return $newArray;
    }
}
