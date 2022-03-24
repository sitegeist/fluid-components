# Fluid Components: Component Context (advanced feature)

Since version 3.2.0, each component has a context object which stores information about the component's rendering context.
It can be accessed in the component via `{component.context}` and can be accessed in data structures by implementing the
`ComponentContextAware` interface (see `Labels` for an example implementation). Each component inherits the context of its
parent component, which means that you can define a context for a component and all of its children.

By default, the component context doesn't do anything, unless you set one of the available options.
Context options can be set in two ways:

* `<fc:context>` ViewHelper can be wrapped around component calls in a fluid template
* `ComponentRenderer::renderComponent()` if you render individual components programmatically via PHP

The following options are currently supported, additional options are planned for the future:

## Language override

The component context will be used in instances of the [`Labels` data structure](./DataStructures.md) to override the current language.
You can define a `languageKey` as well as an array of `alternativeLanguageKeys`, which will be passed to `LocalizationUtility::translate()`
internally.

**Components/Welcome/Welcome.html:**

```xml
<fc:component>
    <fc:param name="labels" type="Labels" optional="1" /><!-- Provides language labels fron xlf files in component folder -->

    <fc:renderer>
        <strong>{labels.welcome}</strong>
    </fc:renderer>
</fc:component>
```

**Fluid example:**

```xml
<!-- will output translation labels in the current language, e. g. English -->
<my:welcome />
<!-- output: Welcome -->

<!-- will output translation labels in the specified language, German -->
<fc:context languageKey="de">
    <my:welcome />
</fc:context>
<!-- output: Willkommen -->
```

**PHP example:**

```php
$componentContext = GeneralUtility::makeInstance(ComponentContext::class)
    ->setLanguageKey('de');
$arguments = [];
$renderedComponent = ComponentRenderer::renderComponent(
    $arguments,
    function () {
        return '';
    },
    GeneralUtility::makeInstance(RenderingContext::class),
    \Vendor\MyExtension\Components\Welcome::class,
    $componentContext
);
```
