<?php

namespace SMS\FluidComponents\Utility;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use SMS\FluidComponents\ViewHelpers\ComponentViewHelper;
use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

class ViewHelperGenerator extends AbstractViewHelper
{
    protected $componentNamespace;
    protected $parsedTemplate;
    protected $componentArgumentDefinitions = [];

    /**
     * Cache of argument definitions; the key is the ViewHelper class name, and the
     * value is the array of argument definitions.
     *
     * In our benchmarks, this cache leads to a 40% improvement when using a certain
     * ViewHelper class many times throughout the rendering process.
     * @var array
     */
    static protected $componentArgumentDefinitionCache = [];

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('_componentNamespace', 'string', 'Component namespace', true);
    }

    public function handleAdditionalArguments(array $arguments)
    {
        // Arguments have already been validated at compile time
        $this->arguments = array_merge($this->arguments, $arguments);
    }

    public function validateAdditionalArguments(array $arguments)
    {
        $componentArgumentDefinitions = $this->prepareComponentArguments();

        foreach ($arguments as $argumentName => $argumentValue) {
            if (!isset($componentArgumentDefinitions[$argumentName])) {
                $undeclaredArguments[] = $argumentName;
                continue;
            }

            $value = $argumentValue->evaluate($this->renderingContext);
            $argumentDefinition = $componentArgumentDefinitions[$argumentName];
            $type = $argumentDefinition->getType();
            if ($value !== $argumentDefinition->getDefaultValue() && $type !== 'mixed') {
                $givenType = is_object($value) ? get_class($value) : gettype($value);
                if (!$this->isValidType($type, $value)) {
                    throw new \InvalidArgumentException(
                        'The argument "' . $argumentName . '" was registered with type "' . $type . '", but is of type "' .
                        $givenType . '" in component "' . $this->componentNamespace . '".',
                        1256475113
                    );
                }
            }
        }

        if (!empty($undeclaredArguments)) {
            throw new Exception(
                sprintf(
                    'Undeclared arguments passed to component %s: %s. Valid arguments are: %s',
                    $this->componentNamespace,
                    implode(', ', $undeclaredArguments),
                    implode(', ', array_keys($componentArgumentDefinitions))
                )
            );
        }

        foreach ($componentArgumentDefinitions as $argumentName => $argumentDefinition) {
            if (!isset($arguments[$argumentName]) && $argumentDefinition->isRequired() && !$argumentDefinition->getDefaultValue()) {
                throw new Exception(
                    sprintf(
                        'Missing required argument %s for component %s.',
                        $argumentName,
                        $this->componentNamespace
                    )
                );
            }
        }
        
    }

    public function setArguments(array $arguments)
    {
        if (isset($arguments['_componentNamespace'])) {
            $this->setComponentNamespace($arguments['_componentNamespace']);
            unset($arguments['_componentNamespace']);
        }
        parent::setArguments($arguments);
    }

    public function setComponentNamespace($componentNamespace)
    {
        $this->componentNamespace = $componentNamespace;
    }

    public function getComponentNamespace()
    {
        return $this->componentNamespace;
    }

    public function getComponentName()
    {
        $namespace = explode('\\', $this->componentNamespace);
        $componentName = end($namespace);
        return implode(' ', [$namespace[0], $namespace[1], $componentName]);
    }

    public function getComponentPrefix()
    {
        return GeneralUtility::underscoredToLowerCamelCase(
            str_replace(' ', '_', $this->getComponentName())
        );
    }

    public function render()
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $variableContainer = $renderingContext->getVariableProvider();

        $variableContainer->add('component', [
            'namespace' => $this->getComponentNamespace(),
            'name' => $this->getComponentName(),
            'prefix' => $this->getComponentPrefix()
        ]);

        foreach ($this->arguments as $name => $value) {
            $variableContainer->add($name, $value);
        }

        if (!$this->getParsedTemplate) {
            $componentLoader = $this->getComponentLoader();
            $componentFile = $componentLoader->findComponent($this->componentNamespace);

            $this->parsedTemplate = $renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
                $this->getTemplateIdentifier(),
                function () use ($componentFile) {
                    // TODO change this to use fluid methods?
                    return file_get_contents($componentFile);
                }
            );
        }

        return $this->parsedTemplate->render($renderingContext);
    }

    /**
     * You only should override this method *when you absolutely know what you
     * are doing*, and really want to influence the generated PHP code during
     * template compilation directly.
     *
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return sprintf(
            '%s::renderComponent(%s, %s, $renderingContext, %s)',
            get_class($this),
            $argumentsName,
            $closureName,
            "'" . addslashes($this->componentNamespace) . "'"
        );
    }

    /**
     * Default implementation of static rendering; useful API method if your ViewHelper
     * when compiled is able to render itself statically to increase performance. This
     * default implementation will simply delegate to the ViewHelperInvoker.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @param string $componentNamespace
     * @return mixed
     */
    public static function renderComponent(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext, $componentNamespace)
    {
        $arguments['_componentNamespace'] = $componentNamespace;
        return static::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }

    protected function registerComponentArgument($name, $type, $description, $required = false, $defaultValue = null)
    {
        if (array_key_exists($name, $this->componentArgumentDefinitions)) {
            throw new Exception(
                'Argument "' . $name . '" has already been defined for component ' . $this->componentNamespace . ', thus it should not be defined again.',
                1253036401
            );
        }
        $this->componentArgumentDefinitions[$name] = new ArgumentDefinition($name, $type, $description, $required, $defaultValue);
        return $this;
    }

    protected function initializeComponentArguments()
    {
        $componentLoader = $this->getComponentLoader();
        $componentFile = $componentLoader->findComponent($this->componentNamespace);

        $this->parsedTemplate = $this->renderingContext->getTemplateParser()->parse(
            // TODO change this to use fluid methods?
            file_get_contents($componentFile),
            $this->getTemplateIdentifier()
        );

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
                $this->registerComponentArgument($param['name'], $param['type'], '', !$optional, $param['default']);
            }
        }
    }

    /**
     * Initialize all arguments and return them
     *
     * @return ArgumentDefinition[]
     */
    protected function prepareComponentArguments()
    {
        if (isset(self::$componentArgumentDefinitionCache[$this->componentNamespace])) {
            $this->componentArgumentDefinitions = self::$componentArgumentDefinitionCache[$this->componentNamespace];
        } else {
            $this->initializeComponentArguments();
            self::$componentArgumentDefinitionCache[$this->componentNamespace] = $this->componentArgumentDefinitions;
        }
        return $this->componentArgumentDefinitions;
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

    protected function getTemplateIdentifier()
    {
        return 'fluidcomponent_' . $this->componentNamespace;
    }

    /**
     * @return ComponentLoader
     */
    protected function getComponentLoader()
    {
        return GeneralUtility::makeInstance(ComponentLoader::class);
    }
}