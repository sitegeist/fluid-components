<?php

namespace SMS\FluidComponents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;
use SMS\FluidComponents\Utility\ComponentLoader;

class ComponentViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Component name');
    }

    /**
     * @return null
     */
    public function render()
    {
        return $this->renderChildren();
        //$componentLoader = new ComponentLoader();
/*
        foreach ($autoloader[0]->getPrefixes() as $prefix => $paths) {
            $componentLoader->add($prefix, $paths);
        }
        foreach ($autoloader[0]->getPrefixesPsr4() as $prefix => $paths) {
            $componentLoader->addPsr4($prefix, $paths);
        }

        $componentLoader->addClassMap($autoloader[0]->getClassMap());
        
        $componentLoader->add('', $autoloader[0]->getFallbackDirs());
        $componentLoader->addPsr4('', $autoloader[0]->getFallbackDirsPsr4());
        */
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
        //     //$findFileWithExtension->invoke($originalAutoloader, \SMS\FluidComponents\Components\UserProfile::class, '.html')
        //     $componentLoader->findComponent(\SMS\FluidComponents\Components\UserProfile::class)
        // , "SPR");
        return null;
    }
}