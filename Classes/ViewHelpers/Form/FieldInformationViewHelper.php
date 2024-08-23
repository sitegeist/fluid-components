<?php
namespace SMS\FluidComponents\ViewHelpers\Form;

use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;

/**
 * This ViewHelper serves as glue code between TYPO3/ExtBase form handling and Fluid Components. It separates
 * the concerns "presentation" and "integration" by simulating a form element without actually rendering it.
 * The ViewHelper takes care of ExtBase's form magic (e. g. trusted properties), while Fluid Components are
 * responsible for the actual markup.
 *
 * <code title="Prepares variables for children">
 *   <fc:form.fieldInformation
 *     property="{element.identifier}"
 *     as="field"
 *     additionalAttributes="{formvh:translateElementProperty(element: element, property: 'fluidAdditionalAttributes')}"
 *   >
 *     <input
 *       type="text"
 *       placeholder="{field.additionalAttributes.placeholder}"
 *       name="{field.name}"
 *       value="{field.value}"
 *      />
 *   </fc:form.fieldInformation>
 * </code>
 *
 * Attributes of variable 'field':
 * field = [
 *    additionalAttributes => [
 *         placeholder => 'First name'
 *         required => 'required'
 *         minlength => '1'
 *         maxlength => '2'
 *     ]
 *     data => NULL
 *     name => 'tx_form_formframework[register-168][text-2]'
 *     value => 'A'
 *     property => 'text-2'
 *     nameWithoutPrefix => 'register-168[text-2]',
 *     formIdentifier => register-168
 * ]
 *
 * @package SMS\FluidComponents\ViewHelpers\Form
 * @author Alexander Bohndorf <bohndorf@sitegeist.de>
 */
class FieldInformationViewHelper extends AbstractFormFieldViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'as',
            'string',
            'Name of the variable that should contain information about the current form field',
            false,
            'fieldInformation'
        );
    }

    /**
     * Provides information about the form field to the child elements of this ViewHelper
     */
    public function render(): string
    {
        // Get form context if available
        $formRuntime = $this->renderingContext
            ->getViewHelperVariableContainer()
            ->get(RenderRenderableViewHelper::class, 'formRuntime');

        $properties = $this->arguments;
        unset($properties['as']);

        // Get raw field properties
        $properties['value'] = $this->getValueAttribute();
        $properties['name'] = $this->getName();
        $properties['nameWithoutPrefix'] = $this->getNameWithoutPrefix();
        $properties['formIdentifier'] = ($formRuntime) ? $formRuntime->getFormDefinition()->getIdentifier() : null;
        $properties['prefix'] = $this->getPrefix();

        // Provide form properties to children (e. g. components)
        $this->templateVariableContainer->add($this->arguments['as'], $properties);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($this->arguments['as']);

        return $output;
    }

    /**
     * @return string prefix/namespace
     */
    protected function getPrefix(): string
    {
        if (!$this->viewHelperVariableContainer->exists(\TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class, 'fieldNamePrefix')) {
            return '';
        }
        $fieldNamePrefix = (string)$this->viewHelperVariableContainer->get(\TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::class, 'fieldNamePrefix');
        return $fieldNamePrefix;
    }
}
