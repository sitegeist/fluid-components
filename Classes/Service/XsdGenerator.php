<?php

namespace SMS\FluidComponents\Service;

use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class XsdGenerator
{
    /**
     * @var object|\SMS\FluidComponents\Utility\ComponentLoader
     */
    private $componentLoader;

    public function __construct(ComponentLoader $componentLoader)
    {
        $this->componentLoader = $componentLoader;
    }

    /**
     * @param $componentName Name of component without namespace, f.e. 'atom.button'
     * @param ArgumentDefinition[] $arguments
     * @return string
     */
    protected function generateXsdForComponent($componentName, $arguments)
    {
        $xsd = '<xsd:element name="' . $componentName . '">
        <xsd:annotation>
            <xsd:documentation><![CDATA[Component ' . $componentName . ']]></xsd:documentation>
        </xsd:annotation>
        <xsd:complexType mixed="true">
            <xsd:sequence>
                <xsd:any minOccurs="0" maxOccurs="unbounded"/>
            </xsd:sequence>';
        foreach ($arguments as $argumentName => $argumentDefinition) {
            $requiredTag = $argumentDefinition->isRequired() ? ' use="required"' : '';
            try {
                $defaultTag = (string)$argumentDefinition->getDefaultValue() !== '' ? ' default="' . $argumentDefinition->getDefaultValue() . '"' : '';
            } catch (\Exception $e) {
                $defaultTag = '';
            }
            $xsd .= "\n" . '            <xsd:attribute type="xsd:string" name="' . $argumentDefinition->getName() . '"' . $requiredTag . $defaultTag . '>
                <xsd:annotation>
                    <xsd:documentation><![CDATA[' . $argumentDefinition->getDescription() . ']]></xsd:documentation>
                </xsd:annotation>
            </xsd:attribute>';
        }
        $xsd .= '</xsd:complexType>
    </xsd:element>';
        return $xsd;
    }

    protected function convertNameSpaceToPathSegment($namespace)
    {
        return str_replace('\\', '/', $namespace);
    }

    protected function getTargetXMLNameSpace($namespace)
    {
        return 'http://typo3.org/ns/' . $this->convertNameSpaceToPathSegment($namespace);
    }

    protected function generateXsdForNamespace($namespace, $components)
    {
        $xsd = '<?xml version="1.0" encoding="UTF-8"?><xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            targetNamespace="' . $this->getTargetXMLNameSpace($namespace) . '">' . "\n";
        foreach ($components as $componentName => $componentFile) {
            $componentRenderer = GeneralUtility::makeInstance(ComponentRenderer::class);
            $componentRenderer->setComponentNamespace($componentName);
            $arguments = $componentRenderer->prepareArguments();
            $componentNameWithoutNameSpace = $this->getTagName($namespace, $componentName);
            $xsd .= $this->generateXsdForComponent($componentNameWithoutNameSpace, $arguments);
        }
        $xsd .= '</xsd:schema>' . "\n";
        return $xsd;
    }

    private function getTagName($nameSpace, $componentName)
    {
        $tagName = '';
        if (strpos($componentName, $nameSpace) === 0) {
            $tagNameWithoutNameSpace = substr($componentName, strlen($nameSpace) + 1);
            $tagName = lcfirst(str_replace('\\', '.', $tagNameWithoutNameSpace));
        }
        return $tagName;
    }

    /**
     * returns only the upper chars of a given string
     *
     * @param $string
     * @return string
     */
    private function strUpperChars($string)
    {
        $output = '';
        $strLength = strlen($string);
        for ($i = 0; $i < $strLength; $i++) {
            if (ctype_upper($string[$i])) {
                $output .= $string[$i];
            }
        }
        return $output;
    }

    /**
     * returns default prefix for a namespace if defined
     * otherwise it builds a prefix from the extension name part of the namespace
     *
     * @param $namespace
     * @return int|string
     */
    protected function getDefaultPrefixForNamespace($namespace)
    {
        $defaultNamespaceDefinitions = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'];
        foreach ($defaultNamespaceDefinitions as $prefix => $registeredNameSpaces) {
            foreach ($registeredNameSpaces as $registeredNameSpace) {
                if ($registeredNameSpace === $namespace) {
                    return $prefix;
                }
            }
        }
        // no registered default prefix found, so build one from extension name part of the namespace
        // f.e. Vendor\MyExtension\Components => me (converting only the upper chars from 'MyExtension' to lower case
        $nameSpaceParts = explode('\\', $namespace);
        $lastFragment = $nameSpaceParts[1];
        return strtolower($this->strUpperChars($lastFragment));
    }

    /**
     * generate xsd file for each component namespace
     *
     * @param $path
     * @param null $namespace
     * @return array Array of generated XML target namespaces
     */
    public function generateXsd($path, $namespace = null)
    {
        $generatedNameSpaces = [];
        $namespaces = $this->componentLoader->getNamespaces();
        foreach ($namespaces as $registeredNamespace => $registeredNamepacePath) {
            if ($namespace === null || $registeredNamespace === $namespace) {
                $components = $this->componentLoader->findComponentsInNamespace($registeredNamespace);
                $filePath = rtrim($path, DIRECTORY_SEPARATOR) .
                    DIRECTORY_SEPARATOR .
                    $this->getFileNameForNamespace($registeredNamespace);
                file_put_contents($filePath, $this->generateXsdForNamespace($registeredNamespace, $components));
                $generatedNameSpaces[$this->getDefaultPrefixForNamespace($registeredNamespace)][] = $this->getTargetXMLNameSpace($registeredNamespace);
            }
        }
        return $generatedNameSpaces;
    }

    /**
     * returns a default filename for a given namespace
     *
     * @param $namespace
     * @return string
     */
    protected function getFileNameForNamespace($namespace)
    {
        return str_replace('\\', '_', $namespace) . '.xsd';
    }
}
