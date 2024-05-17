<?php

call_user_func(function () {
    // Make fc a global namespace
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

    // Register type aliases
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'] = array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'] ?? [],
        [
            'DateTime' => \SMS\FluidComponents\Domain\Model\DateTime::class,
            'File' => \SMS\FluidComponents\Domain\Model\File::class,
            'Image' => \SMS\FluidComponents\Domain\Model\Image::class,
            'Labels' => \SMS\FluidComponents\Domain\Model\Labels::class,
            'Link' => \SMS\FluidComponents\Domain\Model\Link::class,
            'Navigation' => \SMS\FluidComponents\Domain\Model\Navigation::class,
            'NavigationItem' => \SMS\FluidComponents\Domain\Model\NavigationItem::class,
            'Slot' => \SMS\FluidComponents\Domain\Model\Slot::class,
            'Typolink' => \SMS\FluidComponents\Domain\Model\Typolink::class,
        ]
    );

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidComponents.partialsInComponents'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidComponents.partialsInComponents'] = false;
    }
});
