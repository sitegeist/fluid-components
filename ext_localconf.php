<?php

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver::class] = [
        'className' => \SMS\FluidComponents\Fluid\ViewHelper\ViewHelperResolver::class
    ];

    // Make fc a global namespace
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Image'] = \SMS\FluidComponents\Domain\Model\Image::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Link'] = \SMS\FluidComponents\Domain\Model\Link::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Typolink'] = \SMS\FluidComponents\Domain\Model\Typolink::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['Navigation'] = \SMS\FluidComponents\Domain\Model\Navigation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases']['NavigationItem'] = \SMS\FluidComponents\Domain\Model\NavigationItem::class;
});
