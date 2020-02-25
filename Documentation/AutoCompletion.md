# Fluid Components: Auto Completion

## Cli Task
This extension comes with a cli task to generate xsd files for your components.
You can generate these files and reference them in your ide to activate syntax auto-completion for your
components in your fluid templates.

Generate xsd files of all components in the current directory:

```
$ bin/typo3 fluidcomponents:generatexsd .
```

The parameter -v adds more verbosity and informs about the xmlns attributes you have to add to the `html` tag in your
fluid templates to activate syntax auto-completion.

```
bin/typo3 fluidcomponents:generatexsd . -v
```

## Generate xsd for a dedicated namespace

```
$ bin/typo3 fluidcomponents:generatexsd . --namespace='Vendor\\MyExtension\\Components' -v
```

The namespace should be quoted with single quotes and each backlash has to be doubled. Don't add a leading backslash
in name space.

**It is good practice to include a xsd file for all your components in your extension, f.e. in directory `Documentation/Xsd`.**
 
## Header in your Fluid Templates

You have to add an xmlns declarion inside of the html tag to activate syntax auto-completion in your fluid
template.

In the following example the namespace `me` is referenced to all of the fluid components defined in the extension
`MyExtension`.

```xml
<html 
xmlns:me="http://typo3.org/ns/Vendor/MyExtension/Components"
data-namespace-typo3-fluid="true">
</html>
```

Inside this you can reference all your components inside your extension with view helpers like `<me:atom.button />`.
