<?php

namespace SMS\FluidComponents\Fluid\ViewHelper;

use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\Utility\ComponentPrefixer\ComponentPrefixerInterface;
use SMS\FluidComponents\Utility\ComponentPrefixer\GenericComponentPrefixer;
use SMS\FluidComponents\Utility\ComponentSettings;
use SMS\FluidComponents\ViewHelpers\ComponentViewHelper;
use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class ComponentRenderer extends AbstractViewHelper
{
    protected $reservedArguments = [
        'class',
        'component',
        'content',
        'settings',
    ];

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
     * Cache of component argument definitions; the key is the component namespace, and the
     * value is the array of argument definitions.
     *
     * In our benchmarks, this cache leads to a 40% improvement when using a certain
     * ViewHelper class many times throughout the rendering process.
     * @var array
     */
    protected static $componentArgumentDefinitionCache = [];

    /**
     * Cache of component prefixer objects
     *
     * @var array
     */
    protected static $componentPrefixerCache = [];

    /**
     * Components are HTML markup which should not be escaped
     *
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Children should be treated just like an argument
     *
     * @var boolean
     */
    protected $escapeChildren = false;

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
     * Returns the component prefix
     *
     * @return string
     */
    public function getComponentClass()
    {
        return $this->getComponentPrefixer()->prefix($this->componentNamespace);
    }

    /**
     * Returns the component prefix
     *
     * @return string
     */
    public function getComponentPrefix()
    {
        return $this->getComponentClass() . $this->getComponentPrefixer()->getSeparator();
    }

    /**
     * Renders the component the viewhelper is responsible for
     * TODO this can probably be improved by using renderComponent() directly
     *
     * @return void
     */
    public function render()
    {
        // Create a new rendering context for the component file
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $renderingContext->setControllerContext($this->renderingContext->getControllerContext());
        $renderingContext->setViewHelperVariableContainer($this->renderingContext->getViewHelperVariableContainer());
        $variableContainer = $renderingContext->getVariableProvider();

        // Provide information about component to renderer
        $variableContainer->add('component', [
            'namespace' => $this->componentNamespace,
            'class' => $this->getComponentClass(),
            'prefix' => $this->getComponentPrefix(),
        ]);
        $variableContainer->add('settings', $this->getComponentSettings());

        // Provide supplied arguments from component call to renderer
        $componentArgumentConverter = $this->getComponentArgumentConverter();
        foreach ($this->arguments as $name => $argument) {
            $argumentType = $this->argumentDefinitions[$name]->getType();

            $argument = $componentArgumentConverter->convertValueToType($argument, $argumentType);
            $variableContainer->add($name, $argument);
        }

        // Provide component content to renderer
        if (!isset($this->arguments['content'])) {
            $variableContainer->add('content', $this->renderChildren());
        }

        // Initialize component rendering template
        if (!isset($this->parsedTemplate)) {
            $componentLoader = $this->getComponentLoader();
            $componentFile = $componentLoader->findComponent($this->componentNamespace);

            $this->parsedTemplate = $renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
                $this->getTemplateIdentifier($componentFile),
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
     * Replacement for renderStatic() to provide component namespace to ViewHelper
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
        $viewHelperClassName = get_called_class();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $componentRenderer = $objectManager->get($viewHelperClassName);
        $componentRenderer->setComponentNamespace($componentNamespace);

        return $renderingContext->getViewHelperInvoker()->invoke(
            $componentRenderer,
            $arguments,
            $renderingContext,
            $renderChildrenClosure
        );
    }

    /**
     * Initializes the component arguments based on the component definition
     *
     * @return void
     * @throws Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'class',
            'string',
            'Additional CSS classes for the component'
        );
        $this->registerArgument(
            'content',
            'string',
            'Main content of the component; falls back to ViewHelper tag content'
        );

        $this->initializeComponentParams();
    }

    /**
     * Initialize all arguments and return them
     *
     * @return ArgumentDefinition[]
     */
    public function prepareArguments()
    {
        // Store caches for components separately because they can't be grouped by class name
        if (isset(self::$componentArgumentDefinitionCache[$this->componentNamespace])) {
            $this->argumentDefinitions = self::$componentArgumentDefinitionCache[$this->componentNamespace];
        } else {
            $this->initializeArguments();
            self::$componentArgumentDefinitionCache[$this->componentNamespace] = $this->argumentDefinitions;
        }
        return $this->argumentDefinitions;
    }

    /**
     * Default implementation of validating additional, undeclared arguments.
     * In this implementation the behavior is to consistently throw an error
     * about NOT supporting any additional arguments. This method MUST be
     * overridden by any ViewHelper that desires this support and this inherited
     * method must not be called, obviously.
     *
     * @throws Exception
     * @param array $arguments
     * @return void
     */
    public function validateAdditionalArguments(array $arguments)
    {
        if (!empty($arguments)) {
            throw new Exception(
                sprintf(
                    'Undeclared arguments passed to component %s: %s. Valid arguments are: %s',
                    $this->componentNamespace,
                    implode(', ', array_keys($arguments)),
                    implode(', ', array_keys($this->argumentDefinitions))
                ),
                1530632359
            );
        }
    }

    /**
     * Validate arguments, and throw exception if arguments do not validate.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateArguments()
    {
        $componentArgumentConverter = $this->getComponentArgumentConverter();
        $argumentDefinitions = $this->prepareArguments();
        foreach ($argumentDefinitions as $argumentName => $registeredArgument) {
            if ($this->hasArgument($argumentName)) {
                $value = $this->arguments[$argumentName];
                $defaultValue = $registeredArgument->getDefaultValue();
                $type = $registeredArgument->getType();
                if ($value !== $defaultValue && $type !== 'mixed') {
                    $givenType = is_object($value) ? get_class($value) : gettype($value);
                    if (!$this->isValidType($type, $value)
                        && !$componentArgumentConverter->canTypeBeConvertedToType($givenType, $type)
                    ) {
                        throw new \InvalidArgumentException(
                            'The argument "' . $argumentName . '" was registered with type "' . $type . '", but is of type "' .
                            $givenType . '" in component "' . $this->componentNamespace . '".',
                            1530632537
                        );
                    }
                }
            }
        }
    }

    /**
     * Creates ViewHelper arguments based on the params defined in the component definition
     *
     * @return void
     */
    protected function initializeComponentParams()
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);

        $componentLoader = $this->getComponentLoader();
        $componentFile = $componentLoader->findComponent($this->componentNamespace);

        // Parse component template without using the cache
        $parsedTemplate = $renderingContext->getTemplateParser()->parse(
            file_get_contents($componentFile),
            $this->getTemplateIdentifier($componentFile)
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

            // Register argument definitions from parameter viewhelpers
            foreach ($paramNodes as $paramNode) {
                $param = [];
                foreach ($paramNode->getArguments() as $argumentName => $argumentNode) {
                    $param[$argumentName] = $argumentNode->evaluate($renderingContext);
                }

                // Use tag content as default value instead of attribute
                if (!isset($param['default'])) {
                    $param['default'] = implode('', array_map(function ($node) use ($renderingContext) {
                        return $node->evaluate($renderingContext);
                    }, $paramNode->getChildNodes()));
                }

                if (in_array($param['name'], $this->reservedArguments)) {
                    throw new Exception(sprintf(
                        'The argument "%s" defined in "%s" cannot be used because it is reserved.',
                        $param['name'],
                        $this->getComponentNamespace()
                    ), 1532960145);
                }

                // Resolve type aliases
                $param['type'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['typeAliases'][$param['type']] ?? $param['type'];

                // Enforce boolean node, see implementation in ViewHelperNode::rewriteBooleanNodesInArgumentsObjectTree()
                if ($param['type'] === 'boolean' || $param['type'] === 'bool') {
                    $param['default'] = (bool) $param['default'];
                // Make sure that default value for object parameters is either a valid object or null
                } elseif (class_exists($param['type']) && !$param['default'] instanceof $param['type']) {
                    $param['default'] = null;
                }

                $optional = $param['optional'] ?? false;
                $description = $param['description'] ?? '';
                $this->registerArgument($param['name'], $param['type'], $description, !$optional, $param['default']);
            }
        }
    }

    /**
     * Extract all ViewHelpers of a certain type from a Fluid template node
     *
     * @param NodeInterface $node
     * @param string $viewHelperClassName
     * @return void
     */
    protected function extractViewHelpers(NodeInterface $node, string $viewHelperClassName)
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
    protected function getTemplateIdentifier(string $templateFile)
    {
        return 'fluidcomponent_' . $this->componentNamespace . '_' . sha1_file($templateFile);
    }

    /**
     * Returns the prefixer object responsible for the current component namespaces
     *
     * @return ComponentPrefixerInterface
     */
    protected function getComponentPrefixer()
    {
        if (!isset(self::$componentPrefixerCache[$this->componentNamespace])) {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer'])) {
                arsort($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']);
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer'] as $namespace => $prefixer) {
                    $namespace = ltrim($namespace, '\\');
                    if (strpos($this->componentNamespace, $namespace) === 0) {
                        $componentPrefixerClass = $prefixer;
                        break;
                    }
                }
            }

            if (!$componentPrefixerClass) {
                $componentPrefixerClass = GenericComponentPrefixer::class;
            }

            $componentPrefixer = GeneralUtility::makeInstance($componentPrefixerClass);

            if (!($componentPrefixer instanceof ComponentPrefixerInterface)) {
                throw new Exception(sprintf(
                    'Invalid component prefixer: %s',
                    $componentPrefixerClass
                ), 1530608357);
            }

            self::$componentPrefixerCache[$this->componentNamespace] = $componentPrefixer;
        }

        return self::$componentPrefixerCache[$this->componentNamespace];
    }

    /**
     * @return ComponentLoader
     */
    protected function getComponentLoader(): ComponentLoader
    {
        return GeneralUtility::makeInstance(ComponentLoader::class);
    }

    /**
     * @return ComponentSettings
     */
    protected function getComponentSettings(): ComponentSettings
    {
        return GeneralUtility::makeInstance(ComponentSettings::class);
    }

    /**
     * @return ComponentArgumentConverter
     */
    protected function getComponentArgumentConverter(): ComponentArgumentConverter
    {
        return GeneralUtility::makeInstance(ComponentArgumentConverter::class);
    }
}
