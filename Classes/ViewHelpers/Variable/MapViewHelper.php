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
        $this->registerArgument('keysMap', 'array', 'Map of keys.');
        $this->registerArgument('keepFields', 'string', 'Comma separated list of fields to keep in array.');
        $this->registerArgument('subject', 'mixed', 'The array or Object to remap.');
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
        $mapKeyArray = $arguments['keysMap'] ?? [];
        $keepFields = array_map('trim', explode(',', $arguments['keepFields'] ?? ''));

        $newArray = [];
        foreach ($subject as $item) {
            $newItem = [];

            foreach ($mapKeyArray as $replace => $key) {
                $newItem[$replace] = VariableExtractor::extract($item, $key);

                //Set static string value from Key if Key not found
                if (is_null($newItem[$replace])) {
                    $newItem[$replace] = $key;
                }
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
