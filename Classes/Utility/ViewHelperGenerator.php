<?php

namespace SMS\FluidComponents\Utility;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use SMS\FluidComponents\ViewHelpers\ComponentViewHelper;
use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;

class ViewHelperGenerator extends AbstractViewHelper
{
    protected $componentName;
    protected $componentFile;
    protected $parsedTemplate;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initialize()
    {
        //$this->parseComponent();
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        if (!$this->renderingContext) {
            return;
        }

        $this->parseComponent();

        $componentNodes = $this->extractViewHelpers(
            $this->parsedTemplate->getRootNode(),
            ComponentViewHelper::class
        );

        if (count($componentNodes) > 1) {
            throw new \Exception('Only one component per file allowed');
        }

        if (!empty($componentNodes)) {
            $paramNodes = $this->extractViewHelpers(
                $componentNodes[0],
                ParamViewHelper::class
            );

            foreach ($componentNodes[0]->getArguments() as $argumentName => $argumentValue) {
                if ($argumentName === 'name') {
                    $this->componentName = $argumentValue->evaluate($this->renderingContext);
                }
            }

            $renderingContext = $this->renderingContext;
            
            foreach ($paramNodes as $paramNode) {
                $param = [];
                foreach ($paramNode->getArguments() as $argumentName => $argumentNode) {
                    $param[$argumentName] = $argumentNode->evaluate($this->renderingContext);
                }
                if (!isset($param['default'])) {
                    $param['default'] = implode('', array_map(function ($node) use ($renderingContext) {
                        return $node->evaluate($renderingContext);
                    }, $paramNode->getChildNodes()));
                }

                $optional = $param['optional'] ?? false;
                $this->registerArgument($param['name'], $param['type'], '', !$optional, $param['default']);
            }
        }
    }

    public function setComponentFile($componentFile)
    {
        $this->componentFile = $componentFile;
    }

    public function render()
    {
        $this->templateVariableContainer->add('component', [
            'name' => $this->componentName,
            'prefix' => lcfirst($this->componentName)
        ]);

        foreach ($this->arguments as $name => $value) {
            $this->templateVariableContainer->add($name, $value);
        }

        return $this->parsedTemplate->render($this->renderingContext);
    }

    protected function parseComponent()
    {
        if ($this->parsedTemplate) {
            return;
        }

        $componentFile = $this->componentFile;
        $this->parsedTemplate = $this->renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
            $componentFile,
            function () use ($componentFile) {
                return file_get_contents($componentFile);
            }
        );
    }

    protected function extractViewHelpers($node, $viewHelperClassName)
    {
        $viewHelperNodes = [];

        if ($node instanceof EscapingNode) {
            $node = $node->getNode();
        }

        if ($node instanceof ViewHelperNode && $node->getViewHelperClassName() === $viewHelperClassName) {
            $viewHelperNodes[] = $node;
        } else {
            foreach ($node->getChildNodes() as $childNode) {
                $viewHelperNodes = array_merge(
                    $viewHelperNodes,
                    $this->extractViewHelpers($childNode, $viewHelperClassName)
                );
            }
        }

        return $viewHelperNodes;
    }
}