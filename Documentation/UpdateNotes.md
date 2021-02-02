# Fluid Components: Updating from 1.x

There is only one breaking change: It isn't possible anymore to use Fluid variables in default values
for component parameters. This will **NOT WORK ANYMORE**:

```xml
<fc:param name="firstName" type="string" optional="1" default="{settings.defaultFirstName}" />
```

As this was a pretty esoteric feature, you shouldn't have any problems when updating to 2.x!
