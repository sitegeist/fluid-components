<?php

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver::class] = array(
        'className' => \SMS\FluidComponents\Fluid\ViewHelperResolver::class
    );

    // Make fc a global namespace
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';
});
