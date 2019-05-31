# Fluid Components: Usage with Forms

There are two ViewHelpers that allow us to separate TYPO3's form logic from presentational components:

* `fc:form.fieldInformation` provides information about the form field to the presentational component
* `fc:form.translatedValidationResults` translates validation results and provides them to the presentational component in a simplified data structure

Both ViewHelpers are documented in the [ViewHelper Reference](./ViewHelperReference.md).

## Example

```xml
<f:form action="create" name="author" object="{author}">
    <fc:form.translatedValidationResults property="firstname" as="validationResults">
        <fc:form.fieldInformation as="field" property="firstname">
            <my:molecule.fieldLabel
                label="{f:translate(key: 'author.firstname')}"
                validationMessages="{validationResults.errors}"
            >
                <my:atom.input
                    hasErrors="{validationResults.hasErrors}"
                    name="{field.name}"
                    value="{field.value}"
                />
            </my:molecule.fieldLabel>
        </fc:form.fieldInformation>
    </fc:form.translatedValidationResults>

    <f:form.submit value="Create new" />
</f:form>
```

Components/Molecule/FieldLabel/FieldLabel.html:

```xml
<fc:component>
    <fc:param name="label" type="string" />
    <fc:param name="validationMessages" type="array" optional="1" />

    <fc:renderer>
        <div class="fieldLabel">
            <label>
                <span class="fieldLabelText">{label}</span>
                {content -> f:format.raw()}
            </label>
            <f:if condition="{validationMessages}">
                <ul class="fieldLabelValidation">
                    <f:for each="{validationMessages}" as="validationMessage">
                        <li>{validationMessage.message}</li>
                    </f:for>
                </ul>
            </f:if>
        </div>
    </fc:renderer>
</fc:component>
```
