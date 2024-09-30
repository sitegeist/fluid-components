<?php declare(strict_types=1);

namespace SMS\FluidComponents\ViewHelpers\Variable;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
 *
 * @author Simon Praetorius <praetorius@sitegeist.de>
 */
class MapViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('fieldMapping', 'array', 'Map of fields keys.');
        $this->registerArgument('keepFields', 'mixed', 'Array or comma separated list of fields to keep in array.');
        $this->registerArgument('subject', 'mixed', 'The array of objects/arrays to remap.');
    }

    /**
     * @return array remapped array
     */
    public function render()
    {
        $subject = $this->arguments['subject'] ?? $this->renderChildren();
        $mapKeyArray = $this->arguments['fieldMapping'] ?? [];
        $keepFields = $this->arguments['keepFields'] ?? [];
        if (!is_array($keepFields)) {
            $keepFields = array_map('trim', explode(',', (string) $keepFields));
        }

        $newArray = [];
        foreach ($subject as $item) {
            $variableProvider = new StandardVariableProvider();
            $variableProvider->setSource($item);

            $newItem = [];
            foreach ($mapKeyArray as $newKey => $oldKey) {
                $newItem[$newKey] = $variableProvider->get($oldKey);
            }

            //Add another fields from keepFields list
            foreach ($keepFields as $key) {
                $newItem[$key] = $variableProvider->get($key);
            }

            $newArray[] = $newItem;
        }

        return $newArray;
    }
}
