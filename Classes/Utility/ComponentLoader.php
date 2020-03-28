<?php

namespace SMS\FluidComponents\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
     * Registers a component package both in fluid components and as global
     * Fluid namespace. This is the recommended way to register components
     * if your extension only provides one folder of components.
     *
     * @param string $extensionKey    extension key
     * @param string $fluidAlias      Namespace alias that exposes components to Fluid templates,
     *                                defaults to the extension key
     * @param string $componentsPath  path in which components are located,
     *                                defaults to Resources/Private/Components
     * @return string                 the generated package namespace for identification of the
     *                                package in Fluid
     */
    public function registerPackage(
        string $extensionKey,
        string $fluidAlias = null,
        string $componentsPath = null
    ): string {
        $fluidAlias = $fluidAlias ?? $extensionKey;
        $componentsPath = $componentsPath ?? implode(DIRECTORY_SEPARATOR, [
            'Resources',
            'Private',
            'Components'
        ]);

        $packageNamespace = $this->generatePackageNamespace($extensionKey);

        // Register component namespace
        $componentsPath = ExtensionManagementUtility::extPath($extensionKey, $componentsPath);
        $this->addNamespace($packageNamespace, $componentsPath);

        // Register global fluid namespace
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$fluidAlias])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$fluidAlias] = [];
        }
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$fluidAlias][] = $packageNamespace;

        return $packageNamespace;
    }

    /**
     * Generates a component package namespace for an extension key
     *
     * @param string $extensionKey
     * @return string
     */
    public function generatePackageNamespace(string $extensionKey): string
    {
        // Generate generic component namespace
        $packageName = GeneralUtility::underscoredToUpperCamelCase($extensionKey);
        return 'SMS\\FluidComponents\\ComponentPackages\\' . $packageName;
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
