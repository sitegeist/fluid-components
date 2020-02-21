<?php

namespace SMS\FluidComponents\Service;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class XsdGenerator
{
    /**
     * @var object|\SMS\FluidComponents\Utility\ComponentLoader
     */
    private $componentLoader;

    public function __construct()
    {
        $this->componentLoader = GeneralUtility::makeInstance(\SMS\FluidComponents\Utility\ComponentLoader::class);
    }

    /**
     * @param $componentName Name of component without namespace, f.e. 'atom.button'
     * @param ArgumentDefinition[] $arguments
     * @return string
     */
    protected function generateXsdForComponent($componentName, $arguments) {
        $xsd = '<xsd:element name="' . $componentName . '">
        <xsd:annotation>
            <xsd:documentation><![CDATA[Component ' . $componentName .']]></xsd:documentation>
        </xsd:annotation>
        <xsd:complexType mixed="true">
            <xsd:sequence>
                <xsd:any minOccurs="0" maxOccurs="unbounded"/>
            </xsd:sequence>';
        foreach ($arguments as $argumentName => $argumentDefinition) {
            $requiredTag = $argumentDefinition->isRequired() ? ' use="required"' : '';
            try {
                $defaultTag = (string)$argumentDefinition->getDefaultValue() !== '' ? ' default="' . $argumentDefinition->getDefaultValue() . '"' : '';
            } catch(\Exception $e) {
                $defaultTag = '';
            }
            $xsd .= "\n" . '           <xsd:attribute type="xsd:string" name="' . $argumentDefinition->getName() .'"' . $requiredTag . $defaultTag . '>
                <xsd:annotation>
                    <xsd:documentation><![CDATA[' . $argumentDefinition->getDescription() . ']]></xsd:documentation>
                </xsd:annotation>
           </xsd:attribute>';
        }
        $xsd .= '</xsd:complexType>
    </xsd:element>';
        return $xsd;
    }

    protected function generateXsdForNamespace($namespace, $components) {

        $namespaceToPath = str_replace('\\', '/', $namespace);
        $xsd = '<?xml version="1.0" encoding="UTF-8"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            targetNamespace="http://typo3.org/ns/' . $namespaceToPath . '">' . "\n";
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
            $tagName = strtolower(str_replace('\\', '.', $tagNameWithoutNameSpace));
        }
        return $tagName;
    }

    public function generateXsd() {
        $output = '';
        $namespaces = $this->componentLoader->getNamespaces();
        foreach($namespaces as $namespace => $path) {
            $components = $this->componentLoader->findComponentsInNamespace($namespace);

            $output .= $this->generateXsdForNamespace($namespace, $components);
        }
        return $output;
    }
}
