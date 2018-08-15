# Fluid Components: Documentation

## Creating a component

Fluid components consist of an html file placed inside a folder which has the same name as the file:

* Components/
    * MyComponent/
        * MyComponent.html

MyComponent.html defines the component's interface and implementation by utilizing a combination of three ViewHelpers:

```html
<fc:component>
    <fc:param ... />
    ...
    <fc:renderer>...</fc:renderer>
</fc:component>
```

The Fluid namespace `fc:` is predefined when the extension is installed, so there is no need to define it manually in each component file.

## ViewHelpers

### Component ViewHelper

The `fc:component` ViewHelper wraps the component definition.

#### Arguments

* `description` (optional): A description of the component for documentation purposes

#### Example

```html
<fc:component description="This is a description of the component">
    ...
</fc:component>
```

### Param ViewHelper

The `fc:param` ViewHelper defines the interface the component provides. Each `fc:param` defines one data input the component needs to be able to render correctly.

There are two predefined parameters that can be used in all components:

* `class`: Additional CSS classes the component should use
* `content`: falls back to the component's tag content

    ```html
    <my:myComponent>This goes into the content parameter</my:myComponent>
    ```

#### Arguments

* `name`: Name of the component parameter
* `type`: Data type of the component parameter. It takes the same values Fluid defines for ViewHelper arguments:
    * `string`
    * `boolean`
    * `integer`
    * `float`
    * `array` or something like `float[]`
    * PHP class names like `DateTime` or `\TYPO3\CMS\Core\Resource\FileInterface`
* `description` (optional): A description of the parameter for documentation purposes
* `optional` (default: `false`): Declares if the parameter can be omitted when using the component
* `default` (optional): A default value that will be used in case an optional parameter was omitted. The default value can alternatively be defined in the `fc:param` tag content.

In addition to static values, the `default` attribute can also contain Fluid variables (mainly `{settings...}`) and ViewHelper calls, although this can lead to unexpected results depending on the ViewHelper used. For example, when using the `f:translate` ViewHelper, you should always specify the `extensionName` attribute.

#### Examples

```html
<!-- required parameter -->
<fc:param name="lastName" type="string" />
<!-- optional parameter -->
<fc:param name="firstName" type="string" optional="1" />
<!-- parameter with description -->
<fc:param name="badges" type="array" description="An array of user badges" />
<!-- parameter with default value -->
<fc:param name="googleApiKey" type="string" optional="1" default="{settings.google.apiKey}" />
<!-- alternative notation -->
<fc:param name="googleApiKey" type="string" optional="1">{settings.google.apiKey}</fc:param>
```

### Renderer ViewHelper

The `fc:renderer` ViewHelper contains the implementation part of the component, namely the markup the component will generate when called. Inside the renderer, every component parameter will have its own variable. In addition, there are a few predefined variables you can use:

* `{content}` and `{class}`: Predefined component parameters, see [Param ViewHelper](#param-viewhelper)

* `{component}` contains metadata of the component. For now, this includes:
    * `{component.namespace}`: Full namespace of the component
    * `{component.class}`: Proposed CSS class for the root element of the component, according to the responsible [ComponentPrefixer](#component-prefixer)
    * `{component.prefix}`: Proposed CSS prefix for sub elements of the component, according to the responsible [ComponentPrefixer](#component-prefixer)
    
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

```html
<fc:renderer>
    <a href="{link}">{content}</a>
</fc:renderer>
```

## Component Prefixers

TODO

* Prefixer
    * Default
    * Custom

## Component Settings

TODO

* Settings
    * TypoScript
    * PHP
* Use cases

---

* Best practices
    * logic vs. presentational
    * colocation
