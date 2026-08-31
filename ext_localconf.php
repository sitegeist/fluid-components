<?php declare(strict_types=1);

use SMS\FluidComponents\Domain\Model\File;
use SMS\FluidComponents\Domain\Model\Image;
use SMS\FluidComponents\Domain\Model\Labels;
use SMS\FluidComponents\Domain\Model\Link;
use SMS\FluidComponents\Domain\Model\Navigation;
use SMS\FluidComponents\Domain\Model\NavigationItem;
use SMS\FluidComponents\Domain\Model\Slot;
use SMS\FluidComponents\Domain\Model\Typolink;

call_user_func(function (): void {
    // Make fc a global namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'] ??= [];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fc'][] = 'SMS\\FluidComponents\\ViewHelpers';

    // Register type aliases
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'] = array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'] ?? [],
        [
            'File' => File::class,
            'Image' => Image::class,
            'Labels' => Labels::class,
            'Link' => Link::class,
            'Navigation' => Navigation::class,
            'NavigationItem' => NavigationItem::class,
            'Slot' => Slot::class,
            'Typolink' => Typolink::class,
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidComponents.partialsInComponents'] ??= false;
});
