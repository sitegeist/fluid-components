# Fluid Components

This TYPO3 extensions puts frontend developers in a position to create encapsulated components
in pure Fluid. By defining a clear interface (API) for the integration, frontend developers can
implement components independent of backend developers. The goal is to create presentational
components which have no side effects and aren't responsible for data acquisition. The sole
concern of a presentational component should be how things look.

## Authors & Sponsors

* Simon Praetorius - praetorius@sitegeist.de
* [All contributors](https://github.com/sitegeist/fluid-components/graphs/contributors)

*The development and the public-releases of this package is generously sponsored
by my employer https://sitegeist.de.*

## Installation

This TYPO3 extension is available via packagist:

```composer require sitegeist/fluid-components```

Alternatively, you can install the extension from TER:

[TER: fluid_components](https://typo3.org/extensions/repository/view/fluid_components)

After that, proceed with [Getting Started](#getting-started)

## What does it do?

Fluid templates usually consist of three parts:

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

The following component implements a simple teaser element:

*Components/Teaser/Teaser.html*

```xml
<fc:component>
    <fc:param name="title" type="string" />
    <fc:param name="description" type="string" />
    <fc:param name="link" type="string" />
    <fc:param name="icon" type="string" optional="1" />
    <fc:param name="theme" type="string" optional="1">light</fc:param>

    <fc:renderer>
        <a href="{link}" class="{component.class} {component.class}-{theme}">
            <h3 class="{component.prefix}title">{title}</h3>
            <p class="{component.prefix}description">{description}</p>

            <f:if condition="{icon}">
                <i class="icon icon-{icon} {component.prefix}icon"></i>
            </f:if>
        </a>
    </fc:renderer>
</fc:component>
```

Use the following code in your template to render a teaser about TYPO3:

```xml
{namespace my=VENDOR\MyExtension\Components}
<my:teaser
    title="TYPO3"
    description="The professional, flexible Content Management System"
    link="https://typo3.org"
    icon="typo3"
/>
```

The result is the following HTML:

```xml
<a href="https://typo3.org" class="smsExampleTeaser smsExampleTeaser-light">
    <h3 class="smsExampleTeaser_title">TYPO3</h3>
    <p class="smsExampleTeaser_description">The professional, flexible Content Management System</p>

    <i class="icon icon-typo3 smsExampleTeaser_icon"></i>
</a>
```
*(improved indentation for better readability)*

## Why should I use components?

* Components encourage **markup reusage and refactoring**. Only the component knows about its implementation
details. As long as the interface stays compatible, the implementation can change.
* Components can be a tool to **enforce design guidelines**. If the component's implementation respects the
guidelines, they are respected everywhere the component is used.
* Components **formalize and improve communication**. Frontend developers and integrators agree on a clearly
defined interface instead of debating implementation details.
* Components **reduce dependencies**. Frontend developers can work independent of integrators and backend developers.

## Getting Started

1. [Install the extension](#installation)

2. Define the component namespace in your *ext_localconf.php*:

	```php
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces']['VENDOR\\MyExtension\\Components'] =
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('my_extension', 'Resources/Private/Components');
	```

	Use your own vendor name for `VENDOR`, extension name for `MyExtension`, and extension key for `my_extension`.

3. Create your first component in *EXT:my_extension/Resources/Private/Components/* by creating a directory
*MyComponent* containing a file *MyComponent.html*

4. Define your component according to [How do components look like?](#how-do-components-look-like) as well as
the [Documentation](Documentation/Documentation.md).

5. Render your component by including the namespace and calling the component by its name:

	```xml
	{namespace my=VENDOR\MyExtension\Components}
	<my:myComponent someParameter="someValue" />
	```

## Documentation

[Go to the documentation](Documentation/Documentation.md)
