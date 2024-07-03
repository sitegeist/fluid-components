<?php

namespace SMS\FluidComponents\Utility;

use SMS\FluidComponents\Domain\Model\Component;

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
        // Sanitize namespace data
        $namespace = $this->sanitizeNamespace($namespace);
        $path = $this->sanitizePath($path);

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
        // Make sure that namespaces are sanitized
        $this->namespaces = [];
        foreach ($namespaces as $namespace => $path) {
            $this->addNamespace($namespace, $path);
        }

        // Order by namespace specificity
        krsort($this->namespaces);

        return $this;
    }

    /**
     * Returns all registered component namespaces
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Finds a component file based on its class namespace
     *
     * @param string $class
     * @param string $ext
     * @return Component|null
     */
    public function findComponent(string $class, string $ext = '.html')
    {
        // Try cache first
        $cacheIdentifier = $class . '|' . $ext;
        if (isset($this->componentsCache[$cacheIdentifier])) {
            return $this->componentsCache[$cacheIdentifier];
        }

        // Walk through available namespaces, ordered from specific to unspecific
        $class = ltrim($class, '\\');
        foreach ($this->namespaces as $namespace => $path) {
            // No match, skip to next
            if (strpos($class, $namespace . '\\') !== 0) {
                continue;
            }

            $componentName = trim(substr($class, strlen($namespace)), '\\');
            $componentParts = explode('\\', $componentName);

            $componentPath = $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $componentParts);
            $componentFile = $componentPath . DIRECTORY_SEPARATOR . end($componentParts) .  $ext;

            // Check if component file exists
            if (file_exists($componentFile)) {
                $component = new Component($class, $namespace, $componentName, $componentFile);
                $this->componentsCache[$cacheIdentifier] = $component;
                return $component;
            }
        }

        return null;
    }

    /**
     * Provides a list of all components that are available in the specified component namespace
     *
     * @param string $namespace
     * @param string $ext
     * @return array  Array of components where the keys contain the component identifier (FQCN)
     *                and the values contain the path to the component
     */
    public function findComponentsInNamespace(string $namespace, string $ext = '.html'): array
    {
        if (!isset($this->namespaces[$namespace]) || !is_dir($this->namespaces[$namespace])) {
            return [];
        }

        $scannedPaths = [];
        return $this->scanForComponents(
            $this->namespaces[$namespace],
            $ext,
            $namespace,
            $scannedPaths
        );
    }

    /**
     * Searches for component files in a directory and maps them to their namespace
     *
     * @param string $path
     * @param string $ext
     * @param string $namespace
     * @param array $scannedPaths  Collection of paths that have already been scanned for components;
     *                             this prevents infinite loops caused by circular symlinks
     * @return array
     */
    protected function scanForComponents(string $path, string $ext, string $namespace, array &$scannedPaths): array
    {
        $components = [];

        $componentCandidates = scandir($path);
        foreach ($componentCandidates as $componentName) {
            // Skip relative links
            if ($componentName === '.' || $componentName === '..') {
                continue;
            }

            // Only search for directories and prevent infinite loops
            $componentPath = realpath($path . DIRECTORY_SEPARATOR . $componentName);
            if (!is_dir($componentPath) || isset($scannedPaths[$componentPath])) {
                continue;
            }
            $scannedPaths[$componentPath] = true;

            $componentNamespace = $namespace . '\\' . $componentName;
            $componentFile = $componentPath . DIRECTORY_SEPARATOR . $componentName . $ext;

            // Only match folders that contain a component file
            if (file_exists($componentFile)) {
                $components[$componentNamespace] = $componentFile;
            }

            // Continue recursively
            $components = array_merge(
                $components,
                $this->scanForComponents($componentPath, $ext, $componentNamespace, $scannedPaths)
            );
        }

        return $components;
    }

    /**
     * Sanitizes a PHP namespace for use in the component loader
     *
     * @param string $namespace
     * @return string
     */
    protected function sanitizeNamespace(string $namespace): string
    {
        return trim($namespace, '\\');
    }

    /**
     * Sanitizes a path for use in the component loader
     *
     * @param string $path
     * @return string
     */
    protected function sanitizePath(string $path): string
    {
        return rtrim($path, DIRECTORY_SEPARATOR);
    }
}
