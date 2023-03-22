# Fluid Components: Data Structures

One key aspect of components is the clearly defined interface between the integration
and the the component renderer via parameters. By default, Fluid
and Fluid Components support scalar data types, such as `string` or `integer`, and the
compound type `array` for arguments. But it is also possible to pass PHP objects to
ViewHelpers or components.

This allows us to define complex data structures which make the component interface
more formal but also more flexible during integration.

## Links and Typolink

`SMS\FluidComponents\Domain\Model\Link` (alias: `Link`)
`SMS\FluidComponents\Domain\Model\Typolink` (alias: `Typolink`)

TYPO3's backend link wizards generate a link definition in the so-called Typolink format:

```
t3://page?uid=123 _blank myCssClass "Title of the link"
```

It contains the link itself, but also allows you to specify a target, a link title, and additional css classes.

When a component takes a link as one of its parameters, it should on the one hand be able
to use a Typolink, but on the other hand it should be able to use a simple HTTP url. This
is where the Typolink datastructure as well as argument converters come into play:

Our sample component `Atom.Link`:

```xml
<fc:component>
    <fc:param name="label" type="string" />
    <fc:param name="link" type="Typolink" />

    <fc:renderer>
        <a href="{link.uri}" target="{link.target}" title="{link.title}" class="{link.class}">
            {label}
        </a>
    </fc:renderer>
</fc:component>
```

This component can be called in the following ways thanks to the implicit conversion
Fluid Components offers:

```xml
<my:atom.link
    label="Link to sitegeist website"
    link="https://sitegeist.de/"
/>

<my:atom.link
    label="Link to email"
    link="mailto:test@example.com"
/>

<my:atom.link
    label="Link to TYPO3 page with uid 123"
    link="123"
/>

<my:atom.link
    label="Link to TYPO3 page with alias 'myalias'"
    link="t3://page?alias=myalias"
/>

<my:atom.link
    label="Link to TYPO3 page with uid 456 in new window"
    link="456 _blank"
/>
<my:atom.link
    label="Link to TYPO3 page with uid 456 in new window"
    link="{uri: 456, target: '_blank'}"
/>

<my:atom.link
    label="Link to TYPO3 page with uid 789 with title and CSS class"
    link='789 - myCssClass "This is my link title"'
/>
<my:atom.link
    label="Link to TYPO3 page with uid 789 with title and CSS class"
    link="{uri: 789, class: 'myCssClass', title: 'This is my link title'}"
/>
```

In addition to the Typolink parsing, the Link data structure also gives access to the individual parts
of the URI by using `parse_url`. You get access to the following link properties in the component renderer:

* `originalLink`: TYPO3 link data structure, e. g. `['type' => 'page', 'pageuid' => 1]`
* `target`: e. g. `_blank`
* `class`: additional CSS classes that should be set on the `<a>` tag
* `title`: title attribute of the `<a>` tag
* `uri`: complete url, e. g. `https://myuser:mypassword@www.example.com:8080/my/path/file.jpg?myparam=1#myfragment`
* `scheme`: e. g. `https`
* `host`: e. g. `www.example.com`
* `port`: e. g. `8080`
* `user`: e. g. `myuser`
* `pass`: e. g. `mypassword`
* `path`: e. g. `/my/path/file.jpg`
* `query`: e. g. `myparam=1`
* `fragment`: e. g. `myfragment`

## Files and Images

Components should be able to accept a file or image as a parameter, no matter where it come from. TYPO3 already has data structures
for files that are stored in the File Abstraction Layer. However, there are cases where you want to use
files from inside extensions or even external image urls. This is what the File/Image data structures offer:

for images:

* `SMS\FluidComponents\Domain\Model\Image` (alias: `Image`) is the base class of all image types as well as a factory
* `SMS\FluidComponents\Domain\Model\RemoteImage` wraps a remote image uri
* `SMS\FluidComponents\Domain\Model\FalImage` wraps existing FAL objects and local image resources, such as `File` and `FileReference` or an image from an extension
* `SMS\FluidComponents\Domain\Model\PlaceholderImage` generates a placeholder image via placeholder.com

for files:

* `SMS\FluidComponents\Domain\Model\File` (alias: `File`) is the base class of all file types as well as a factory
* `SMS\FluidComponents\Domain\Model\RemoteFile` wraps a remote file uri
* `SMS\FluidComponents\Domain\Model\FalFile` wraps existing FAL objects and local files, such as `File` and `FileReference` or a file from an extension

This is how it could look like in the `Atom.Image` component:

```xml
<fc:component>
    <fc:param name="image" type="Image" />
    <fc:param name="width" type="integer" optional="1" />
    <fc:param name="height" type="integer" optional="1" />

    <fc:renderer>
        <f:switch expression="{image.type}">
            <f:case value="FalImage">
                <f:image
                    image="{image.file}"
                    alt="{image.alternative}"
                    title="{image.title}"
                    width="{width}"
                    height="{height}"
                />
            </f:case>
            <f:defaultCase>
                <img
                    src="{image.publicUrl}"
                    alt="{image.alternative}"
                    title="{image.title}"
                    width="{width}"
                    height="{height}"
                />
            </f:defaultCase>
        </f:switch>
    </fc:renderer>
</fc:component>
```

And these are the different options to call that component:

```xml
<!-- Use static images (local path) -->
<my:atom.image image="EXT:my_extension/Resources/Public/Images/logo.png" />
<my:atom.image image="{file: 'EXT:my_extension/Resources/Public/Images/logo.png'}" />
<my:atom.image image="{resource: {path: 'Images/logo.png', extensionName: 'myExtension'}}" />
<my:atom.image image="{resource: {path: 'Images/logo.png', extensionKey: 'my_extension'}}" />

<!-- Use static images (remote uri) -->
<my:atom.image image="https://www.example.com/my/image.jpg" />
<my:atom.image image="{file: 'https://www.example.com/my/image.jpg'}" />

<!-- Use existing FAL objects -->
<my:atom.image image="{fileObject}" />
<my:atom.image image="{fileObject: fileObject}" />
<my:atom.image image="{fileReferenceObject}" />
<my:atom.image image="{fileObject: fileReferenceObject}" />

<!-- Use FAL file uid -->
<my:atom.image image="123" />
<my:atom.image image="{fileUid: 123}" />

<!-- Use FAL file reference uid -->
<my:atom.image image="{fileReferenceUid: 123}" />

<!-- Use FAL file reference based on table, field, uid, counter -->
<my:atom.image image="{
    fileReference: {
        tableName: 'pages',
        fieldName: 'media',
        uid: 123,
        counter: 0
    }
}" />

<!-- Use a placeholder image with certain dimensions -->
<my:atom.image image="{width: 1000, height: 750}" />

<!-- Add title and alternative text to all array variants -->
<my:atom.image image="{
    fileReferenceUid: 456,
    title: 'My Image Title',
    alternative: 'My Alternative Text'
}" />
```

Inside the component renderer, you get access to the following image properties,
no matter which image variant is used:

* `type`: name of the image implementation, e. g. `PlaceholderImage`
* `publicUrl`: url that can be used in an `<img src...` attribute
* `alternative`: alt text for the image, to be used in `<img alt...`
* `title`: title for the image, to be used in `<img title...`
* `copyright`: copyright text for the image
* `properties`: array of all fields of `sys_file_metadata` (and its override via `sys_file_reference`)

In addition, the different implementations offer additional properties that can
be used safely after checking the `type` property accordingly.

## Translations

`SMS\FluidComponents\Domain\Model\Labels` (alias: `Labels`)

Each component can define its own set of translation labels. Those labels can be accessed via an
instance of the `Labels` class that can needs to be defined as an optional component parameter:

Fluid Component `Molecule/TeaserCard/TeaserCard.html`:

```xml
<fc:component>
    <fc:param name="labels" type="Labels" optional="1" />
    <fc:renderer>
        [...]
        <a href="...">{labels.readMore}</a>
    </fc:renderer>
</fc:component>
```

The `readMore` language label can be defined in an XLIFF translation file that is placed next to
the component html file. Its file name must be *{ComponentName}.labels.xlf* (or *de.{ComponentName}.labels.xlf*
for German translations), so in this example the component will pick up the following language file:

Language file `Molecule/TeaserCard/TeaserCard.labels.xlf`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.0" xmlns:t3="http://typo3.org/schemas/xliff">
    <file source-language="en" datatype="plaintext" original="messages" date="2021-02-02T18:55:32Z" product-name="my_extension">
        <header/>
        <body>
            <trans-unit id="readMore">
                <source>Read more</source>
            </trans-unit>
        </body>
    </file>
</xliff>
```

All labels can also be overwritten when the component is called:

```xml
<my:molecule.teaserCard labels="{readMore: 'Overwritten read more'}" />
```

Please note that due to the way those labels are accessed, it is not possible to use `.` in the
label identifier. We recommend to use lower camel case (`readMore`) or snake case (`read_more`) in
component language files.

## Navigations

`SMS\FluidComponents\Domain\Model\Navigation` (alias: `Navigation`)
`SMS\FluidComponents\Domain\Model\NavigationItem` (alias: `NavigationItem`)

TYPO3 generates navigations by using TypoScript. Nowadays, the modern approach is to use
Data Processors, namely the MenuProcessor. It has various configuration parameters that are consistent
to the *old* TypoScript approach, but provides an array to the Fluid template.

To pass this array structure to your components, you can use the `Navigation` and `NavigationItem` data
structures that are part of Fluid Components. `NavigationItem` represents one item in a navigation, while
`Navigation` represents a whole navigation. `NavigationItems` can have children, which are represented by
a `Navigation` object as well.

Example navigation in TypoScript:

```
page.10 = FLUIDTEMPLATE
page.10 {
    templateName = TestNavigation
    dataProcessing {
        10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
        10 {
            special = directory
            special.value = {$homePage}
            levels = 1
            as = headerMenu
        }
    }
}
```

Fluid Template `TestNavigation.html`:

```xml
<my:molecule.navigation items="{headerMenu}" />
```

Fluid Component `Molecule/Navigation/Navigation.html`:

```xml
<fc:component>
    <fc:param name="items" type="Navigation" />
    <fc:renderer>
        <ul>
            <f:for each="{items}" as="item">
                <li>
                    <a href="{item.link}" target="{item.target}">
                        <f:if condition="{item.active}">
                            <f:then><strong>{item.title}</strong></f:then>
                            <f:else>{item.title}</f:else>
                        </f:if>
                    </a>
                </li>
            </f:for>
        </ul>
    </fc:renderer>
</fc:component>
```

You can also create a navigation manually in your template and simply omit the attributes you don't need:

```xml
<my:molecule.navigation items="{
    0: { title: 'Simple item', link: 123 },
    1: { title: 'Current item', link: 456, current: 1 },
    2: { title: 'External item', link: 'https://domain.tld/path/to/page.html', target: '_blank' },
    3: { title: 'Item with subitems', children: {
        0: { title: 'Subitem', link: 23 }
    }}
}" />
```

## DateTime

`SMS\FluidComponents\Domain\Model\DateTime`

This is a more comfortable implementation, compared to the DateTime class.

Parameters of this type accept 

* a DateTime object,
* a string ('2020-07-07 08:29:33') or
* an integer (1594110573).

This type makes sense if, for example, so-called fixtures are used as data sources within a FluidStyleguide. In the data source (yaml, json, etc.) there is no adequate way to define DateTime objects.

Fluid Template `Event.html` with a native DateTime object:

```xml
<my:molecule.event date="{event.date}" />
```

Fluid Component `Molecule/Event/Event.html`:

```xml
<fc:component>
    <fc:param name="date" type="SMS\FluidComponents\Domain\Model\DateTime" />
    <fc:renderer>
        {date -> f:format.date(format: "%e. %B %Y")}
    </fc:renderer>
</fc:component>
```

Fluid Component Fixture `Molecule/Event/Event.fixtures.yaml`  with a string, which should be converted to DateTime::

```yaml
default:
  date: '2020-07-07 08:29:33'
```

Fluid Component Fixture `Molecule/Event/Event.fixtures.yaml` with an integer, which should be converted to DateTime:

```yaml
default:
  date: 1594110573
```

## Slots

Similar to frontend frameworks [such as Vue.js](https://vuejs.org/guide/components/slots.html), Slots are a data structure that allows you to pass
arbitrary HTML markup to a component. The default `{content}` parameter is implemented as a slot, but you can add your own slots. Slots behave
differently compared to other parameters in the way escaping works. If a parameter defined as a Slot gets passed a Fluid variable, the content
of that variable will be escaped, which makes sure that malicious user input doesn't lead to XSS injections.

To output a slot, you should **always** use the `<fc:slot />` ViewHelper which takes care of a safe HTML output. **format.raw() should no longer be used in Fluid Components since v3.5.0!**

Fluid Component `Molecule/TeaserCard/TeaserCard.html`:

```xml
<fc:component>
    <fc:param name="buttons" type="Slot" optional="1" />
    <fc:renderer>
        <fc:slot name="buttons" />
    </fc:renderer>
</fc:component>
<>
```

```xml
<my:molecule.teaserCard buttons="<button>read more about ABC</button><button>read more about DEF</button>" />

<!-- doesn't work with malicious user input -->
<f:variable name="insecure">
    <script>alert('hacked')</script>
</f:variable>
<my:molecule.teaserCard buttons="<button>read more about ABC</button>{insecure}" />
```

You can also use the `<fc:content />` ViewHelper to specify HTML markup more easily:

```xml
<my:molecule.teaserCard>
    <fc:content slot="buttons">
        <button>read more about ABC</button>
        <button>read more about DEF</button>
    </fc:content>
</my:molecule.teaserCard>
```

## Type Aliases

The included data structures can also be defined with their alias. These are `Image`, `Link`, `Typolink`, `Labels`, `Navigation` and `NavigationItem`.

```xml
<fc:component>
    <fc:param name="image" type="Image" />
</fc:component>
```

To register aliases for other classes extend the typeAliases array in your `ext_localconf.php` (e.g. Phonenumber)

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Phonenumber'] = \VENDOR\MyExtension\Domain\Model\Phonenumber::class;
```
