<?php

namespace SMS\FluidComponents\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * This ViewHelper takes an array of Objects/Arrays
 * and return a new array with the new keys
 * configured in fieldMapping
 * and optionally the existing fields specified in the keepFields.
 *
 * <code title="Provides items for slider, for example in Mask template">
 *   items="{data.tx_mask_sliders -> fc:variable.map(fieldMapping: {image: 'tx_mask_slider_image.0', content: 'tx_mask_slider_text'})}"
 * </code>
 *
 * <code title="Provides navigation items">
 *   {myNavigation -> fc:variable.map(fieldMapping: {url: 'link', title: 'data.page_extend_field'}, keepFields: 'data, target')}
 * </code>
 *
 * @package SMS\FluidComponents\ViewHelpers\Variable
 * @author Simon Praetorius <praetorius@sitegeist.de>
 */
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
        $this->registerArgument('fieldMapping', 'array', 'Map of fields keys.');
        $this->registerArgument('keepFields', 'mixed', 'Array or comma separated list of fields to keep in array.');
        $this->registerArgument('subject', 'mixed', 'The array of objects/arrays to remap.');
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
        $mapKeyArray = $arguments['fieldMapping'] ?? [];
        $keepFields = $arguments['keepFields'] ?? [];
        if (!is_array($keepFields)) {
            $keepFields = array_map('trim', explode(',', $keepFields));
        }

        $newArray = [];
        foreach ($subject as $item) {
            $newItem = [];

            foreach ($mapKeyArray as $newKey => $oldKey) {
                $newItem[$newKey] = VariableExtractor::extract($item, $oldKey);
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
