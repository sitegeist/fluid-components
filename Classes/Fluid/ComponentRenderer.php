<?php

namespace SMS\FluidComponents\Fluid;

use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\ViewHelpers\ComponentViewHelper;
use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class ComponentRenderer extends AbstractViewHelper
{
    /**
     * Namespace of the component the viewhelper should render
     *
     * @var string
     */
    protected $componentNamespace;

    /**
     * Cache for component template instance used for rendering
     *
     * @var \TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface
     */
    protected $parsedTemplate;

    /**
     * Definition objects for all arguments definied in the component definition
     *
     * @var array
     */
    protected $componentArgumentDefinitions = [];

    /**
     * Cache of component argument definitions; the key is the component namespace, and the
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
        $this->registerArgument('_componentNamespace', 'string', 'Component namespace', false);
    }

    /**
     * Provides component arguments to the viewhelper
     *
     * @param array $arguments
     * @return void
     */
    public function handleAdditionalArguments(array $arguments)
    {
        // Arguments have already been validated at compile time
        $this->arguments = array_merge($this->arguments, $arguments);
    }

    /**
     * Validates arguments defined in the component definition
     *
     * @param array $arguments
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateAdditionalArguments(array $arguments)
    {
        $componentArgumentDefinitions = $this->prepareComponentArguments();

        // Validate argument types
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
                    throw new \InvalidArgumentException(sprintf(
                        'The argument "%s" was registered with type "%s", but is of type "%s" in component "%s".',
                        $argumentName,
                        $type,
                        $givenType,
                        $this->componentNamespace
                    ), 1527779337);
                }
            }
        }

        // Don't accept undefined arguments
        if (!empty($undeclaredArguments)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Undeclared arguments passed to component %s: "%s". Valid arguments are: %s',
                    $this->componentNamespace,
                    implode(', ', $undeclaredArguments),
                    implode(', ', array_keys($componentArgumentDefinitions))
                ),
                1527779348
            );
        }

        // Check for missing required arguments
        foreach ($componentArgumentDefinitions as $argumentName => $argumentDefinition) {
            if (!isset($arguments[$argumentName])
                && $argumentDefinition->isRequired()
                && !$argumentDefinition->getDefaultValue()
            ) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Missing required argument "%s" for component %s.',
                        $argumentName,
                        $this->componentNamespace
                    ),
                    1527779365
                );
            }
        }
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function setArguments(array $arguments)
    {
        // Extract component namespace from argument
        if (isset($arguments['_componentNamespace'])) {
            $this->setComponentNamespace($arguments['_componentNamespace']);
            unset($arguments['_componentNamespace']);
        }
        parent::setArguments($arguments);
    }

    /**
     * Sets the namespace of the component the viewhelper should render
     *
     * @param string $componentNamespace
     * @return self
     */
    public function setComponentNamespace($componentNamespace)
    {
        $this->componentNamespace = $componentNamespace;
        return $this;
    }

    /**
     * Returns the namespace of the component the viewhelper renders
     *
     * @return void
     */
    public function getComponentNamespace()
    {
        return $this->componentNamespace;
    }

    /**
     * Returns the component name
     *
     * @return string
     */
    public function getComponentName()
    {
        $namespace = explode('\\', $this->componentNamespace);
        $componentName = end($namespace);
        return implode(' ', [$namespace[0], $namespace[1], $componentName]);
    }

    /**
     * Returns the component prefix
     *
     * @return string
     */
    public function getComponentPrefix()
    {
        return GeneralUtility::underscoredToLowerCamelCase(
            str_replace(' ', '_', $this->getComponentName())
        );
    }

    /**
     * Renders the component the viewhelper is responsible for
     * TODO this can probably be improved by using renderStatic() directly
     *
     * @return void
     */
    public function render()
    {
        // Create a new rendering context for the component file
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $variableContainer = $renderingContext->getVariableProvider();

        // Provide information about component to renderer
        $variableContainer->add('component', [
            'namespace' => $this->componentNamespace,
            'name' => $this->getComponentName(),
            'prefix' => $this->getComponentPrefix()
        ]);

        // Provide supplied arguments from component call to renderer
        // TODO fetch and use default values here if necessary!
        foreach ($this->arguments as $name => $value) {
            if ($name !== '_componentNamespace') {
                $variableContainer->add($name, $value);
            }
        }

        // Initialize component rendering template
        if (!isset($this->parsedTemplate)) {
            $componentLoader = $this->getComponentLoader();
            $componentFile = $componentLoader->findComponent($this->componentNamespace);

            $this->parsedTemplate = $renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
                $this->getTemplateIdentifier(),
                function () use ($componentFile) {
                    return file_get_contents($componentFile);
                }
            );
        }

        // Render component
        return $this->parsedTemplate->render($renderingContext);
    }

    /**
     * Overwrites original compilation to store component namespace in compiled templates
     *
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile(
        $argumentsName,
        $closureName,
        &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ) {
        return sprintf(
            '%s::renderComponent(%s, %s, $renderingContext, %s)',
            get_class($this),
            $argumentsName,
            $closureName,
            var_export($this->componentNamespace, true)
        );
    }

    /**
     * Wrapper around renderStatic() to provide component namespace to ViewHelper via special argument
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @param string $componentNamespace
     * @return mixed
     */
    public static function renderComponent(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
        $componentNamespace
    ) {
        $arguments['_componentNamespace'] = $componentNamespace;
        return static::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }

    /**
     * Registers a new argument for a component
     *
     * @param string $name
     * @param string $type
     * @param string $description
     * @param boolean $required
     * @param mixed $defaultValue
     * @return self
     * @throws Exception
     */
    protected function registerComponentArgument($name, $type, $description, $required = false, $defaultValue = null)
    {
        if (array_key_exists($name, $this->componentArgumentDefinitions)) {
            throw new Exception(sprintf(
                'Argument "%s" has already been defined for component %s, thus it should not be defined again.',
                $name,
                $this->componentNamespace
            ), 1527779384);
        }
        $this->componentArgumentDefinitions[$name] = new ArgumentDefinition(
            $name,
            $type,
            $description,
            $required,
            $defaultValue
        );
        return $this;
    }

    /**
     * Initializes the component arguments based on the component definition
     *
     * @return void
     * @throws Exception
     */
    protected function initializeComponentArguments()
    {
        // TODO use different rendering context here?
        $componentLoader = $this->getComponentLoader();
        $componentFile = $componentLoader->findComponent($this->componentNamespace);

        // Parse component template without using the cache
        $parsedTemplate = $this->renderingContext->getTemplateParser()->parse(
            file_get_contents($componentFile),
            $this->getTemplateIdentifier()
        );

        // Extract all component viewhelpers
        $componentNodes = $this->extractViewHelpers(
            $parsedTemplate->getRootNode(),
            ComponentViewHelper::class
        );

        if (count($componentNodes) > 1) {
            throw new Exception(sprintf(
                'Only one component per file allowed in: %s',
                $componentFile
            ), 1527779393);
        }

        if (!empty($componentNodes)) {
            // Extract all parameter definitions
            $paramNodes = $this->extractViewHelpers(
                $componentNodes[0],
                ParamViewHelper::class
            );

            $renderingContext = $this->renderingContext;

            // Register argument definitions from parameter viewhelpers
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

    /**
     * Extract all ViewHelpers of a certain type from a Fluid template node
     *
     * @param AbstractNode $node
     * @param string $viewHelperClassName
     * @return void
     */
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

    /**
     * Returns an identifier by which fluid templates will be stored in the cache
     *
     * @return string
     */
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
