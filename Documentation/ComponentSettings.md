# Fluid Components: Component Settings

Each component provides the `{settings}` variable which contains global settings that can affect multiple components.
These settings can be set in multiple ways:

## ext_localconf.php

In your *ext_localconf.php* you can use an array notation to add global component settings. The following example
fetches the settings from a JSON file:

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['settings']['styles'] = json_decode(file_get_contents(
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('my_extension', 'styles.json')
));
```

## Programmatically

In your own PHP code, you can add settings by injecting the responsible settings class:

```php
class MyController
{
    /**
     * @var \SMS\FluidComponents\Utility\ComponentSettings
     */
    public $componentSettings;

    public function myAction()
    {
        $this->componentSettings->set('mySetting', 'myValue');
    }
    
    public function injectComponentSettings(\SMS\FluidComponents\Utility\ComponentSettings $componentSettings)
    {
        $this->componentSettings = $componentSettings;
    }
}
```

## TypoScript

It is also possible to add settings via TypoScript:

```
config.tx_fluidcomponents.settings.mySetting = myValue
```
