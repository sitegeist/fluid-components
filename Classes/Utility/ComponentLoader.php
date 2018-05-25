<?php

namespace SMS\FluidComponents\Utility;

class ComponentLoader
{
    protected $originalAutoloader;
    protected $findFileWithExtension;

    public function __construct()
    {
        $autoloaders = spl_autoload_functions();
        $autoloader = reset($autoloaders);
        
        // Call private method Composer\Autoload\ClassLoader::findFileWithExtension()
        $reflectedAutoloader = new \ReflectionClass($autoloader[0]);

        $this->originalAutoloader = $reflectedAutoloader->getProperty('composerClassLoader');
        $this->originalAutoloader->setAccessible(true);
        $this->originalAutoloader = $this->originalAutoloader->getValue($autoloader[0]);

        $this->findFileWithExtension = new \ReflectionMethod($this->originalAutoloader, 'findFileWithExtension');
        $this->findFileWithExtension->setAccessible(true);
    }

    public function findComponent($class, $ext = '.html')
    {
        $pos = strrpos($class, '\\');
        $component = $class . '\\' . substr($class, $pos + 1);
        return $this->findFileWithExtension($component, $ext);
    }

    protected function findFileWithExtension($class, $ext)
    {
        return $this->findFileWithExtension->invoke(
            $this->originalAutoloader,
            $class,
            $ext
        );
    }
}