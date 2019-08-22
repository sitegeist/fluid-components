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
     * @return string|null
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

            $componentParts = explode('\\', trim(substr($class, strlen($namespace)), '\\'));

            $componentPath = $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $componentParts);
            $componentFile = $componentPath . DIRECTORY_SEPARATOR . end($componentParts) .  $ext;

            // Check if component file exists
            if (file_exists($componentFile)) {
                $this->componentsCache[$cacheIdentifier] = $componentFile;
                return $componentFile;
            }
        }

        return null;
    }

    public function findComponentsInNamespace(string $namespace, string $ext = '.html'): array
    {
        if (!isset($this->namespaces[$namespace])) {
            return [];
        }

        return $this->scanForComponents(
            $this->namespaces[$namespace],
            $ext,
            $namespace
        );
    }

    protected function scanForComponents(string $path, string $ext, string $namespace): array
    {
        $components = [];

        $componentCandidates = scandir($path);
        foreach ($componentCandidates as $componentName) {
            $componentPath = $path . DIRECTORY_SEPARATOR . $componentName;
            if ($componentName === '.' || $componentName === '..' || !is_dir($componentPath)) {
                continue;
            }

            $componentNamespace = $namespace . '\\' . $componentName;
            $componentFile = $componentPath . DIRECTORY_SEPARATOR . $componentName . $ext;

            if (file_exists($componentFile)) {
                $components[$componentNamespace] = $componentFile;
            }

            $components = array_merge(
                $components,
                $this->scanForComponents($componentPath, $ext, $componentNamespace)
            );
        }

        return $components;
    }

    protected function sanitizeNamespace(string $namespace): string
    {
        return trim($namespace, '\\');
    }

    protected function sanitizePath(string $path): string
    {
        return rtrim($path, DIRECTORY_SEPARATOR);
    }
}
