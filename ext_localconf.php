<?php

call_user_func(function () {
    // With TYPO3 11.3, Symfony dependency injection is used to instantiate the ViewHelperResolver,
    // thus Fluid Components uses XCLASS for older TYPO3 versions and a custom ServiceProvider for
    // 11.4 and above. TYPO3 11.3 is not supported.
    if (version_compare(TYPO3_version, '11.3', '<')) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver::class] = [
            'className' => \SMS\FluidComponents\Fluid\ViewHelper\LegacyComponentResolver::class
        ];
    }

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
