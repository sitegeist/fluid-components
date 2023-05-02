<?php

call_user_func(function () {
    // Make fc a global namespace
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

    // Register type aliases
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['File'] = \SMS\FluidComponents\Domain\Model\File::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Image'] = \SMS\FluidComponents\Domain\Model\Image::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Link'] = \SMS\FluidComponents\Domain\Model\Link::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Typolink'] = \SMS\FluidComponents\Domain\Model\Typolink::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Navigation'] = \SMS\FluidComponents\Domain\Model\Navigation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['NavigationItem'] = \SMS\FluidComponents\Domain\Model\NavigationItem::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Labels'] = \SMS\FluidComponents\Domain\Model\Labels::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Slot'] = \SMS\FluidComponents\Domain\Model\Slot::class;

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidComponents.partialsInComponents'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidComponents.partialsInComponents'] = false;
    }
});
