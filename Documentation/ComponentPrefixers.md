# Fluid Components: Component Prefixers

Each component provides a prefixed CSS class derived from the component's name and namespace in the `{component}`
variable. This makes moving or renaming the component easy as the CSS classes change automatically. By default,
Fluid Components uses a generic prefixer class (`SMS\FluidComponents\Utility\ComponentPrefixer\GenericComponentPrefixer`).
For examples, take a look at the [Renderer ViewHelper reference](#renderer-viewhelper).

However, prefixers can be overwritten per namespace, which makes it easy to customize the generated CSS classes.
Your prefixer class needs to implement the interface `SMS\FluidComponents\Utility\ComponentPrefixer\ComponentPrefixerInterface`,
which requires you to define two methods:

```php
    /**
     * Returns the component prefix for the provided component namespaces
     *
     * @param string $namespace
     * @return string
     */
    public function prefix(string $namespace): string;

    /**
     * Returns the separator to be used between prefix and the following string
     *
     * @return string
     */
    public function getSeparator(): string;
```

To bind your custom prefixer class to a namespace, simply add the following line to your *ext_localconf.php* file:

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']['VENDOR\\MyExtension'] =
    \VENDOR\MyExtension\Utility\ComponentPrefixer\MyComponentPrefixer::class;
```
