# Fluid Components

This TYPO3 extensions puts frontend developers in a position to create encapsulated components
in pure Fluid. By defining a clear interface (API) for the integration, frontend developers can
implement components independent of backend developers. The goal is to create highly reusable
presentational components which have no side effects and aren't responsible for data acquisition.

⬇️ **[TL;DR? Get started right away](#getting-started)** ⬇️

## What does it do?

[Fluid](https://github.com/typo3/fluid) templates usually consist of three ingredients:

* **Templates**,
* **Layouts**, which structure and wrap the markup defined in the template, and
* **Partials**, which contain markup snippets to be reused in different templates.

In addition, **ViewHelpers** provide basic control structures and encapsulate advanced rendering and
data manipulation that would otherwise not be possible. They are defined as PHP classes.

The extension adds another ingredient to Fluid: **Components**.

## What are components?

Fluid components are similar to ViewHelpers. The main difference is that they can be defined solely in
Fluid. In a way, they are quite similar to Fluid's partials, but they have a few advantages:

* They provide a **clear interface** via predefined parameters. The implementation is encapsulated in
the component. You don't need to know what the component does internally to be able to use it.
* With semantic component names your templates get more **readable**. This gets even better with
[atomic design](http://bradfrost.com/blog/post/atomic-web-design/) or similar approaches.
* They can easily be used across different TYPO3 extensions because they utilize Fluid's
**namespaces**. No *partialRootPath* needed.

## How do components look like?

The following component implements a simple teaser card element:

*Components/TeaserCard/TeaserCard.html*

```xml
<fc:component>
    <fc:param name="title" type="string" />
    <fc:param name="link" type="Typolink" />
    <fc:param name="icon" type="string" optional="1" />
    <fc:param name="theme" type="string" optional="1" default="light" />

    <fc:renderer>
        <a href="{link}" class="{component.class} {component.class}-{theme}">
            <h3 class="{component.prefix}title">{title}</h3>
            <f:if condition="{content}">
                <p class="{component.prefix}description"><fc:slot /></p>
            </f:if>

            <f:if condition="{icon}">
                <i class="icon icon-{icon} {component.prefix}icon"></i>
            </f:if>
        </a>
    </fc:renderer>
</fc:component>
```

Use the following code in your template to render a teaser card about TYPO3:

```xml
{namespace my=VENDOR\MyExtension\Components}
<my:teaserCard
    title="TYPO3"
    link="https://typo3.org"
    icon="typo3"
>
    The professional, flexible Content Management System
</my:teaserCard>
```

The result is the following HTML:

```xml
<a href="https://typo3.org" class="smsExampleTeaserCard smsExampleTeaserCard-light">
    <h3 class="smsExampleTeaserCard_title">TYPO3</h3>
    <p class="smsExampleTeaserCard_description">The professional, flexible Content Management System</p>

    <i class="icon icon-typo3 smsExampleTeaserCard_icon"></i>
</a>
```
*(improved indentation for better readability)*

## Getting Started

1. Install the extension either [from TER](https://typo3.org/extensions/repository/view/fluid_components)
or [via composer](https://packagist.org/packages/sitegeist/fluid-components):

    ```
    composer require sitegeist/fluid-components
    ```

2. Define the component namespace in your *ext_localconf.php*:

	```php
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces']['VENDOR\\MyExtension\\Components'] =
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('my_extension', 'Resources/Private/Components');
	```

	Use your own vendor name for `VENDOR`, extension name for `MyExtension`, and extension key for `my_extension`.

3. Create your first component in *EXT:my_extension/Resources/Private/Components/* by creating a directory
*MyComponent* containing a file *MyComponent.html*

4. Define and apply your component according to [How do components look like?](#how-do-components-look-like). The [Extended Documentation](#extended-documentation)
can be helpful as well.

5. Check out [Fluid Styleguide](https://github.com/sitegeist/fluid-styleguide), a living styleguide for Fluid Components, and [Fluid Components Linter](https://github.com/sitegeist/fluid-components-linter) to improve the quality and reusability of your components.

If you have any questions, need support or want to discuss components in TYPO3, feel free to join [#ext-fluid_components](https://typo3.slack.com/archives/ext-fluid_components).

## Why should I use components?

* Components encourage **markup reusage and refactoring**. Only the component knows about its implementation
details. As long as the interface stays compatible, the implementation can change.
* Components can be a tool to **enforce design guidelines**. If the component's implementation respects the
guidelines, they are respected everywhere the component is used. A helpful tool to accomplish that is the corresponding
living styleguide: [Fluid Styleguide](https://github.com/sitegeist/fluid-styleguide).
* Components **formalize and improve communication**. Frontend developers and integrators agree on a clearly
defined interface instead of debating implementation details.
* Components **reduce dependencies**. Frontend developers can work independent of integrators and backend developers.

## Extended Documentation

Feature References

* [ViewHelper Reference](Documentation/ViewHelperReference.md)
* [Data Structures](Documentation/DataStructures.md)
    * [Links and Typolink](Documentation/DataStructures.md#links-and-typolink)
    * [Files and Images](Documentation/DataStructures.md#files-and-images)
    * [Translations](Documentation/DataStructures.md#translations)
    * [Navigations](Documentation/DataStructures.md#navigations)
    * [DateTime](Documentation/DataStructures.md#datetime)
    * [Slots](Documentation/DataStructures.md#slots)
* [Component Prefixers](Documentation/ComponentPrefixers.md)
* [Component Settings](Documentation/ComponentSettings.md)

How-To's

* [Usage with Forms](Documentation/Forms.md)
* [Add auto-completion in your IDE](Documentation/AutoCompletion.md)
* [Updating from 1.x](Documentation/UpdateNotes.md)
* [Mitigating XSS issues with 3.5](Documentation/XssIssue.md)

## Authors & Sponsors

* Ulrich Mathes - mathes@sitegeist.de
* Simon Praetorius - moin@praetorius.me
* [All contributors](https://github.com/sitegeist/fluid-components/graphs/contributors)

*The development and the public-releases of this package is generously sponsored
by my employer https://sitegeist.de.*
