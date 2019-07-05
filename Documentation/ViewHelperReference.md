# Fluid Components: ViewHelper Reference

## Component Definition

### Component ViewHelper

The `fc:component` ViewHelper wraps the component definition.

#### Arguments

* `description` (optional): A description of the component for documentation purposes

#### Example

```xml
<fc:component description="This is a description of the component">
    ...
</fc:component>
```

### Param ViewHelper

The `fc:param` ViewHelper defines the interface the component provides. Each `fc:param` defines one data
input the component needs to be able to render correctly.

There are two predefined parameters that can be used in all components:

* `class`: Additional CSS classes the component should use
* `content`: falls back to the component's tag content

    ```xml
    <my:myComponent>This goes into the content parameter</my:myComponent>
    ```

#### Arguments

* `name`: Name of the component parameter
* `type`: Data type of the component parameter. It takes the same values Fluid defines for ViewHelper
arguments:
    * `string`
    * `boolean`
    * `integer`
    * `float`
    * `array` or something like `float[]`
    * PHP class names like `DateTime` or `\TYPO3\CMS\Core\Resource\FileInterface`
* `schema`: JSON schema that should be used for further validation of the parameter.
See [JSON schema validation](#json-schema-validation) for further information.
* `description` (optional): A description of the parameter for documentation purposes
* `optional` (default: `false`): Declares if the parameter can be omitted when using the component
* `default` (optional): A default value that will be used in case an optional parameter was omitted. The
default value can alternatively be defined in the `fc:param` tag content.

In addition to static values, the `default` argument can also contain Fluid variables (mainly `{settings...}`)
and ViewHelper calls, although this can lead to unexpected results depending on the ViewHelper used. For example,
when using the `f:translate` ViewHelper, you should always specify the `extensionName` attribute.

#### Examples

```xml
<!-- required parameter -->
<fc:param name="lastName" type="string" />
<!-- optional parameter -->
<fc:param name="firstName" type="string" optional="1" />
<!-- JSON schema validation (inline) -->
<fc:param name="email" type="string" schema="{ type: 'string', format: 'email' }" />
<!-- JSON schema validation (external file: MyComponent/person.schema.json) -->
<fc:param name="person" type="array" schema="person" />
<!-- parameter with description -->
<fc:param name="badges" type="array" description="An array of user badges" />
<!-- parameter with default value -->
<fc:param name="googleApiKey" type="string" optional="1" default="{settings.google.apiKey}" />
<!-- alternative notation -->
<fc:param name="googleApiKey" type="string" optional="1">{settings.google.apiKey}</fc:param>
```

### Renderer ViewHelper

The `fc:renderer` ViewHelper contains the implementation part of the component, namely the markup the component
will generate when called. Inside the renderer, every component parameter will have its own variable. In addition,
there are a few predefined variables you can use:

* `{content}` and `{class}`: Predefined component parameters, see [Param ViewHelper](#param-viewhelper)

* `{component}` contains metadata of the component. For now, this includes:
    * `{component.namespace}`: Full namespace of the component
    * `{component.class}`: Proposed CSS class for the root element of the component, according to the responsible
    [ComponentPrefixer](#component-prefixer)
    * `{component.prefix}`: Proposed CSS prefix for sub elements of the component, according to the responsible
    [ComponentPrefixer](#component-prefixer)
    
    Example:
  
    ```
    namespace: VENDOR\MyExtension\Components\MyComponent
        class: vendorMycomponent
       prefix: vendorMycomponent_
    ```

* `{settings}`: Global settings that can affect multiple components, see [Component Settings](#component-settings)

#### Arguments

none

#### Examples

```xml
<fc:renderer>
    <a href="{link}">{content}</a>
</fc:renderer>
```

### JSON schema validation

In addition to the simple type validation, component parameters can be validated against a
[JSON schema](https://json-schema.org/). This validation will only be performed in `Development`
context to prevent minor error messages and performance implications in `Production` contexts.

The schema can be defined either inline or point to a `.schema.json` file placed inside the
component folder:

#### Inline schema

The following parameter "email" will be checked for a valid email address:

```xml
<fc:param name="email" type="string" schema="{ type: 'string', format: 'email' }" />
```

#### Schema in a file

The following parameter "user" will be validated against a separate schema file:

*Components/MyComponent/MyComponent.html:*

```xml
<fc:param name="user" type="array" schema="user" />
```

*Components/MyComponent/user.schema.json:*

```json
{
    "type": "object",
    "properties": {
        "firstName": {
            "type": "string"
        },
        "middleName": {
            "type": "string"
        },
        "lastName": {
            "type": "string"
        },
        "email": {
            "type": "string",
            "format": "email"
        }
    },
    "required": [
        "firstName",
        "lastName",
        "email"
    ]
}
```

To call the component, you can either use objects provided by PHP or define them directly in Fluid:

```xml
<my:myComponent user="{firstName: 'Jane', lastName: 'Doe', email: 'doe@example.com'}" />


## Form Handling

### Form.FieldInformation ViewHelper

To be able to separate the concerns of form processing (e. g. by Extbase or EXT:form) and form presentation (rendering and styling an input element), we need a middleware which translates "Extbase form magic" to simple values that can be used with components. That middleware is the `fc:form.fieldInformation` ViewHelper.

#### Arguments

* `name`: Name of the form field
* `value`: Value of the form field
* `additionalAttributes`: Additional attributes the form field should have, e. g. `required`
* `property`: Name of the property in the associated domain object
* `as` (default: `fieldInformation`): Name of the variable that should provide the necessary field information to the content of the ViewHelper

#### Examples

Usage with Extbase:

```xml
<fc:form.fieldInformation
    property="email"
    as="fieldInformation"
>
    <my:atom.input
        name="{fieldInformation.name}"
        value="{fieldInformation.value}"
    />
</fc:form.fieldInformation>
```

Usage with EXT:form

```xml
<fc:form.fieldInformation
    property="{element.identifier}"
    additionalAttributes="{formvh:translateElementProperty(element: element, property: 'fluidAdditionalAttributes')}"
    as="fieldInformation"
>
    <my:atom.input
        placeholder="{fieldInformation.additionalAttributes.placeholder}"
        required="{fieldInformation.additionalAttributes.required}"
        name="{fieldInformation.name}"
        value="{fieldInformation.value}"
    />
</fc:form.fieldInformation>
```

### Form.TranslatedValidationResults ViewHelper

The `fc:form.translatedValidationResults` ViewHelper is responsible for translating Extbase form validation results as well as providing them to components in a *clean* way. This means that components don't need special knowledge about the data structures Extbase uses for form validation and don't need translation functionality. This allows them to be fully presentational.

The ViewHelper provides validation results in the following format:

Single field:

```
[
    'errors' => [
        [
            'message' => 'The username must be at least 3 characters long.',
            'code' => 1238108068,
            'arguments' => [3],
            'title' => ''
        ]
    ],
    'warnings' => [],
    'notices' => [],
    'flattenedErrors' => [],
    'flattenedWarnings' => [],
    'flattenedNotices' => [],
    'hasErrors' => true,
    'hasWarnings' => false,
    'hasNotices' => false
]
```

Whole form:

```
[
    'errors' => [],
    'warnings' => [],
    'notices' => [],
    'flattenedErrors' => [
        'myform.username' => [
            [
                'message' => 'The username must be at least 3 characters long.',
                'code' => 1238108068,
                'arguments' => [3],
                'title' => ''
            ]
        ]
    ],
    'flattenedWarnings' => [],
    'flattenedNotices' => [],
    'hasErrors' => true,
    'hasWarnings' => false,
    'hasNotices' => false
]
```

#### Arguments

* `for` (optional): Field identifier for which validation results should be provided
* `as` (default: `validationResults`): Name of the variable that should provide the validation results to the content of the ViewHelper
* `translatePrefix` (default: `validation.error.`): Prefix for language keys that override default validation messages; The final language key will be `{translatePrefix}.{for}.{errorCode}`; Will be ignored in EXT:form context
* `element` (optional): 
* `extensionName` (optional):
* `languageKey` (optional):
* `alternativeLanguageKeys` (optional):

#### Examples

Usage for all form fields:

```xml
<fc:form.translatedValidationResults as="validationResults">
    <my:molecule.messageBox messages="{validationResults.flattenedErrors}" />
</fc:form.translatedValidationResults>
```

Usage for one individual form field:

```xml
<fc:form.translatedValidationResults for="register.email" as="validationResults">
	<my:molecule.fieldLabel
		validationMessages="{validationResults.errors}"
	>
		<my:atom.input
			hasErrors="{validationResults.hasErrors}"
		/>
	</my:molecule.fieldLabel>
</fc:form.translatedValidationResults>
```

Usage with EXT:form:

```xml
<fc:form.translatedValidationResults field="{field} as="validationResults">
	<my:molecule.fieldLabel
		validationMessages="{validationResults.errors}"
	>
		<my:atom.input
			hasErrors="{validationResults.hasErrors}"
		/>
	</my:molecule.fieldLabel>
</fc:form.translatedValidationResults>
```
