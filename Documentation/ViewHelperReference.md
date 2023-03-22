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
* `content`: falls back to the component's tag content; make sure to output content with the [Slot ViewHelper](#slot-viewhelper)

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
    * PHP class names like `Slot`, `DateTime` or `SMS\FluidComponents\Domain\Model\Image`
* `description` (optional): A description of the parameter for documentation purposes
* `optional` (default: `false`): Declares if the parameter can be omitted when using the component
* `default` (optional): A default value that will be used in case an optional parameter was omitted. The
default value can alternatively be defined in the `fc:param` tag content.

#### Examples

```xml
<!-- required parameter -->
<fc:param name="lastName" type="string" />
<!-- optional parameter -->
<fc:param name="firstName" type="string" optional="1" />
<!-- parameter with description -->
<fc:param name="badges" type="array" description="An array of user badges" />
<!-- parameter with default value -->
<fc:param name="theme" type="string" optional="1" default="dark" />
<!-- alternative notation -->
<fc:param name="theme" type="string" optional="1">dark</fc:param>
<!-- slot parameter -->
<fc:param name="buttons" type="Slot" optional="1" />
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

### Slot ViewHelper

The `fc:slot` ViewHelper provides a safe way to render HTML passed to a component as a slot. It makes
sure that only properly escaped HTML content will be rendered.

#### Arguments

* `name` (default: `content`): Name of the slot parameter that should be rendered
* `default` (optional): Fallback content in case the slot is not defined (e. g. because it's marked as optional). The
default value can alternatively be defined in the `fc:slot` tag content.

#### Examples

```xml
<fc:component>
    <fc:param name="description" type="Slot" />
    <fc:param name="buttons" type="Slot" optional="1" />

    <fc:renderer>
        <div class="description">
            <fc:slot name="description" />
        </div>

        <div class="buttons">
            <fc:slot name="buttons" default="<button>Default button</button>" />
        </div>

        <div class="content">
            <fc:slot>Fallback content</fc:slot>
        </div>
    </fc:renderer>
</fc:component>
```


## Translations

### Translate.Labels ViewHelper

There are cases in which you need to use several translation labels in your component. Usually, you would define an argument in your component which gets an array of translated labels. Although it is possible to create that array manually in Fluid, it can become cumbersome and hard to read.

The `fc:translate.labels` ViewHelper aims to make that fluid code more readable. It takes an array of translation keys and returns an array of translations.

#### Arguments

* `keys`: Array of translation keys that should be resolved. The array items can either be strings (= the translation key) or arrays to specify translation arguments as well as a default value (see examples). Possible sub-array keys:
    * `key`: Translation key
    * `arguments`: Arguments for the translation (sprintf)
    * `default`: Default value if the translation doesn't exist
* `extensionName`: Name of the extension that contains the language file
* `languageKey`: Language key if the current language shouldn't be used
* `alternativeLanguageKeys`: Alternative language keys that should be used as fallback if the translation doesn't exist

#### Examples

```xml
<my:organism.header
    labels="{fc:translate.labels(keys: {
        login: 'Login',
        logout: {key: 'Logout', arguments: {0: user.name}},
        menu: {key: 'Menu', default: 'Menu'}
    })}"
/>
```


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
<fc:form.translatedValidationResults field="{field}" as="validationResults">
	<my:molecule.fieldLabel
		validationMessages="{validationResults.errors}"
	>
		<my:atom.input
			hasErrors="{validationResults.hasErrors}"
		/>
	</my:molecule.fieldLabel>
</fc:form.translatedValidationResults>
```

## Mapping

### Variable.Map ViewHelper

Components should not contain dependencies on how they receive data.
These can be DataProviders, mask  elements or news items, for example.
This ViewHelper takes an array of data items (Objects/Arrays) and make a new array with the new keys configured in fieldMapping and optionally the existing fields specified in the keepFields.

#### Arguments

* `subject` (optional): Source array of data items. If not in arguments then taken from inline construction. If not in arguments then taken from tag content
* `fieldMapping` (optional): Array of mapping keys. (see examples)
    * `key`: New key
    * `value`: Name or dot-separated path to field in source array
* `keepFields` (optional): Array or comma separated list of fields to keep in array. Is optional.

#### Examples

Items for slider, for example in Mask template:
```
items="{data.tx_mask_sliders -> fc:variable.map(fieldMapping: {image: 'tx_mask_slider_image.0', content: 'tx_mask_slider_text'})}"
```

Navigation:
```
{myNavigation -> fc:variable.map(fieldMapping: {url: 'link', title: 'data.page_extend_field'}, keepFields: 'data, target')}
```

### Variable.Push ViewHelper

Adds one variable to the end of the array and returns the result. 
Similar functionality as in v:iterator.push from VHS

#### Arguments

* `item` (optional): Item to push to specified array variable. If not in arguments then taken from tag content
* `name`: Name of variable to extend
* `key` (optional): Key that should be used in the array

#### Examples

```xml
    <f:variable name="tags"></f:variable>
    <f:for each="{newsItem.tags}" as="tag">
        <fc:variable.push name="tags" item="{tag.title}" />
    </f:for>
```
