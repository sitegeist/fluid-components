# Mitigating XSS Issues with Fluid Components 3.5

All versions of Fluid Components before 3.5 were susceptible to Cross-Site Scripting. Version 3.5 of the extension
fixes this issue. Due to the nature of the problem, some changes in your project's Fluid templates might be necessary
to prevent unwanted double-escaping of HTML markup.

## When do you need to take action?

In the following edge case, the behavior of the extension changes and you need to adjust your template files:

* Your component uses the predefined `{content}` parameter AND
* Your component allows HTML markup in the `{content}` parameter by applying `f:format.raw()` before output AND
* You pass a variable containing HTML markup to the component's `{content}` parameter without using `f:format.raw()`, `f:format.html()` or similar.

## Finding potential issues

The extension contains a CLI tool that can help you in finding potential escaping issues in your templates. The tool
picks up template files in all installed extensions (`ext/*/Resources/Private/**/*.html`) automatically, checks all
available components and highlights component calls where changes might be necessary. The tool can be executed via CLI:

```
vendor/bin/typo3 fluidcomponents:checkContentEscaping
```

You get a list of potential issues in your template files, for example:

> public/typo3conf/ext/sitepackage/Resources/Private/Extensions/GridElements/Templates/Accordion.html:
> 
> Component "public/typo3conf/ext/sitepackage/Resources/Private/Components/Organism/Accordion/Accordion.html" expects raw html content, but was called with potentially escaped variables: {data.tx_gridelements_view_column_10}

In this case, you would need to add `f:format.raw()` to the described variable, as described below.

Please note that the tool might also show false positives where you don't need to change anything, for example:

> public/typo3conf/ext/sitepackage/Resources/Private/Components/Organism/NewsArticle/NewsArticle.html:
> 
> Component "public/typo3conf/ext/sitepackage/Resources/Private/Components/Molecule/Featurebar/Featurebar.html" expects raw html content, but was called with potentially escaped variables: {datePublished}

This can be safely ignored because `{datePublished}` shouldn't contain HTML markup and thus can be escaped.

## Fixing double escaping in your templates

Quote Component:

```xml
<fc:component>
    <fc:param name="author" type="string" />
    <fc:renderer>
        <blockquote>
            {content -> f:format.raw()}
            <figcaption>{author}</figcaption>
        </blockquote>
    </fc:renderer>
</fc:component>
```

### Adjustments Necessary

In the following examples you need to adjust your template to prevent double escaping of the HTML.

**⚠️ Please make sure to always check if the variable only contains *safe* HTML markup and no direct user input as this could lead to XSS issues!**

General example:

```xml
<f:variable name="variableContainingHtmlMarkup">some text with <b>html markup</b></f:variable>
<!-- this doesn't work anymore: -->
<my:quote author="Jane Doe">This is a quote that uses a {variableContainingHtmlMarkup}.</my:quote>
<!-- needs to be changed to: -->
<my:quote author="Jane Doe">This is a quote that uses a {variableContainingHtmlMarkup -> f:format.raw()}.</my:quote>
```

This is an example of the issue for a template which uses data processed by gridelements:

```xml
<!-- this doesn't work anymore: -->
<my:quote author="Jane Doe" content="{data.tx_gridelements_view_column_10}" />
<!-- needs to be changed to: -->
<my:quote author="Jane Doe" content="{data.tx_gridelements_view_column_10 -> f:format.raw()}" />
```

It can also happen with custom content elements that deal with RTE fields:

```xml
<!-- this doesn't work anymore: -->
<my:quote author="Jane Doe" content="{data.bodytext}" />
<!-- needs to be changed to: -->
<my:quote author="Jane Doe" content="{data.bodytext -> f:format.html()}" />
```

### No Adjustments Necessary

The following examples are fine and don't need to be changed:

```xml
<my:quote author="Jane Doe">This is a simple quote without any HTML markup</my:quote>
```

```xml
<my:quote author="Jane Doe">This is a simple quote with <b>some HTML markup</b></my:quote>
```

```xml
<my:quote author="Jane Doe">This is a quote that uses <my:fancyText>another component</my:fancyText></my:quote>
```

```xml
<my:quote author="Jane Doe">This is a quote that uses {rteContent -> f:format.html()}.</my:quote>
```

```xml
<f:variable name="variableWithoutHtmlMarkup">just some text</f:variable>
<my:quote author="Jane Doe">This is a quote that uses a {variableWithoutHtmlMarkup}.</my:quote>
```

```xml
<f:variable name="variableContainingHtmlMarkup">some text with <b>html markup</b></f:variable>
<my:quote author="Jane Doe">This is a quote that uses a {variableContainingHtmlMarkup -> f:format.raw()}.</my:quote>
```

## Using the new Slot feature

This change isn't strictly necessary to update to the new version, but is advisable because it can make the usage of external
markup more visible and explicit in your components. Developers are motivated to think about the source of a variable and
the potential XSS issues that can be triggered by rendering external markup.

The following quality rules should be set for your components:

* `f:format.raw()`, `f:format.html()` and similar ViewHelpers should **never** be used inside a component, but rather in the
template calling the component (where you know if the markup is safe)
* `content` that can't contain HTML should be escaped by using the default variable syntax: `{content}`
* `content` that can contain HTML must be rendered using the `fc:slot()` ViewHelper
* To check for the existence of `content`, use the default variable syntax: `<f:if condition="{content}">...`
* other parameters that can contain HTML must be of type `Slot` and must be rendered with `fc:slot()` as well

Improved Quote component:

```xml
<fc:component>
    <fc:param name="author" type="string" />
    <fc:param name="cite" type="Slot" />
    <fc:renderer>
        <blockquote>
            <fc:slot />
            <figcaption>
                {author},
                <cite><fc:slot name="cite" /></cite>
            </figcaption>
        </blockquote>
    </fc:renderer>
</fc:component>
```

Examples:

```xml
<my:quote author="Jane Doe" cite="<a href='https://example.com'>Example.com</a>">
    This is a <b>quote example</b>.
</my:quote>
```

```xml
<f:variable name="cite"><a href="https://example.com">Example.com</a></f:variable>
<my:quote author="Jane Doe" cite="{cite -> f:format.raw()}">
    This is a <b>quote example</b>.
</my:quote>
```
