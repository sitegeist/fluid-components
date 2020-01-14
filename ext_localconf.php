<?php

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver::class] = [
        'className' => \SMS\FluidComponents\Fluid\ViewHelper\ViewHelperResolver::class
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\Core\Rendering\RenderingContext::class] = [
        'className' => \SMS\FluidComponents\Fluid\Rendering\RenderingContext::class
    ];

    // Make fc a global namespace
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['FLUIDCOMPONENT'] =
        \Spiegel\Sitepackage\ContentObject\FluidComponentContentObject::class;
});
