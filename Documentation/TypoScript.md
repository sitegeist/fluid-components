# Using Components with TypoScript

Fluid Components can also be rendered purely in TypoScript:

```
page.10 = FLUIDCOMPONENT
page.10 {
    component = my:atom.button
    arguments {
        label = Test button
    }
    content =
}
```
