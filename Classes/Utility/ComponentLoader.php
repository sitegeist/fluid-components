<?php

namespace SMS\FluidComponents\Utility;

class ComponentLoader implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Registered component namespaces
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     * Cache for class name => component file associations
     *
     * @var array
     */
    protected $componentsCache = [];

    /**
     * Initialize the component loader
     */
    public function __construct()
    {
        $this->setNamespaces(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] ?? []
        );
    }

    /**
     * Adds a new component namespace
     *
     * @param string $namespace
     * @param string $path
     * @return self
     */
    public function addNamespace(string $namespace, string $path): self
    {
        $this->namespaces[$namespace] = $path;
        return $this;
    }

    /**
     * Removes a registered component namespace
     *
     * @param string $namespace
     * @return self
     */
    public function removeNamespace(string $namespace): self
    {
        unset($this->namespaces[$namespace]);
        return $this;
    }

    /**
     * Sets the component namespaces
     *
     * @param array $namespaces
     * @return self
     */
    public function setNamespaces(array $namespaces): self
    {
        // Order by namespace specificity
        arsort($namespaces);
        $this->namespaces = $namespaces;
        return $this;
    }

    /**
     * Finds a component file based on its class namespace
     *
     * @param string $class
     * @param string $ext
     * @return string|null
     */
    public function findComponent(string $class, string $ext = '.html')
    {
        // Try cache first
        if (isset($this->componentsCache[$class])) {
            return $this->componentsCache[$class] . $ext;
        }

        // Walk through available namespaces, ordered from specific to unspecific
        $class = ltrim($class, '\\');
        foreach ($this->namespaces as $namespace => $path) {
            $namespace = ltrim($namespace, '\\');

            // No match, skip to next
            if (strpos($class, $namespace) !== 0) {
                continue;
            }

            $componentParts = explode('\\', trim(substr($class, strlen($namespace)), '\\'));

            $componentPath = rtrim($path, '/') . '/' . implode('/', $componentParts) . '/' . end($componentParts);
            $componentFile = $componentPath . $ext;

            // Check if component file exists
            if (file_exists($componentFile)) {
                $this->componentsCache[$class] = $componentPath;
                return $componentFile;
            }
        }

        return null;
    }
}
