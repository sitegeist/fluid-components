<?php declare(strict_types=1);

namespace SMS\FluidComponents\Fluid\ViewHelper;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SMS\FluidComponents\Domain\Model\RequiredSlotPlaceholder;
use SMS\FluidComponents\Domain\Model\Slot;
use SMS\FluidComponents\Interfaces\ComponentAware;
use SMS\FluidComponents\Interfaces\EscapedParameter;
use SMS\FluidComponents\Interfaces\RenderingContextAware;
use SMS\FluidComponents\Utility\ComponentArgumentConverter;
use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\Utility\ComponentPrefixer\ComponentPrefixerInterface;
use SMS\FluidComponents\Utility\ComponentPrefixer\GenericComponentPrefixer;
use SMS\FluidComponents\Utility\ComponentSettings;
use SMS\FluidComponents\ViewHelpers\ComponentViewHelper;
use SMS\FluidComponents\ViewHelpers\ContentViewHelper;
use SMS\FluidComponents\ViewHelpers\ParamViewHelper;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class ComponentRenderer extends AbstractViewHelper
{
    public const DEFAULT_SLOT = 'content';

    protected $reservedArguments = [
        'class',
        'component',
        self::DEFAULT_SLOT,
        'settings',
    ];

    /**
     * Namespace of the component the viewhelper should render.
     */
    protected string $componentNamespace;

    /**
     * Cache for component template instance used for rendering.
     */
    protected ParsedTemplateInterface $parsedTemplate;

    /**
     * Cache of component argument definitions; the key is the component namespace, and the
     * value is the array of argument definitions.
     *
     * In our benchmarks, this cache leads to a 40% improvement when using a certain
     * ViewHelper class many times throughout the rendering process.
     */
    protected static array $componentArgumentDefinitionCache = [];

    /**
     * Cache of component prefixer objects.
     */
    protected static array $componentPrefixerCache = [];

    /**
     * Components are HTML markup which should not be escaped.
     */
    protected $escapeOutput = false;

    /**
     * Children should be treated just like an argument.
     */
    protected $escapeChildren = true;

    public function __construct(
        protected ComponentLoader $componentLoader,
        protected ComponentSettings $componentSettings,
        protected ComponentArgumentConverter $componentArgumentConverter,
        protected ContainerInterface $container
    ) {
    }

    /**
     * Sets the namespace of the component the viewhelper should render.
     */
    public function setComponentNamespace(string $componentNamespace): self
    {
        $this->componentNamespace = $componentNamespace;
        return $this;
    }

    /**
     * Returns the namespace of the component the viewhelper renders.
     */
    public function getComponentNamespace(): string
    {
        return $this->componentNamespace;
    }

    public function getComponentClass(): string
    {
        return $this->getComponentPrefixer()->prefix($this->componentNamespace);
    }

    public function getComponentPrefix(): string
    {
        return $this->getComponentClass() . $this->getComponentPrefixer()->getSeparator();
    }

    /**
     * Renders the component the viewhelper is responsible for
     * TODO: this can probably be improved by using renderComponent() directly.
     *
     */
    public function render(): string
    {
        // Create a new rendering context for the component file
        $renderingContext = $this->getRenderingContext();

        // set the original request to preserve the request attributes
        // some ViewHelpers expect a ServerRequestInterface or other attributes inside the request
        // e.g. f:uri.action, f:page.action

        if (method_exists($this->renderingContext, 'hasAttribute')) {
            if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
                $renderingContext->setAttribute(
                    ServerRequestInterface::class,
                    $this->renderingContext->getAttribute(ServerRequestInterface::class)
                );
            }
        } else {
            $renderingContext->setRequest($this->renderingContext->getRequest());
        }

        $renderingContext->setViewHelperVariableContainer($this->renderingContext->getViewHelperVariableContainer());
        if (static::shouldUseTemplatePaths()) {
            $renderingContext->getTemplatePaths()->setPartialRootPaths(
                $this->renderingContext->getTemplatePaths()->getPartialRootPaths()
            );
        }
        $variableContainer = $renderingContext->getVariableProvider();

        // Provide information about component to renderer
        $variableContainer->add('component', [
            'namespace' => $this->componentNamespace,
            'class' => $this->getComponentClass(),
            'prefix' => $this->getComponentPrefix(),
        ]);
        $variableContainer->add('settings', $this->componentSettings);

        // Provide supplied arguments from component call to renderer
        foreach ($this->argumentDefinitions as $name => $definition) {
            $argumentType = $definition->getType();

            if (is_a($argumentType, Slot::class, true)) {
                $argument = $this->renderSlot((string) $name);
            } else {
                $argument = $this->arguments[$name] ?? null;
            }

            $argument = $this->componentArgumentConverter->convertValueToType($argument, $argumentType);

            // Provide component namespace to certain data structures
            if ($argument instanceof ComponentAware) {
                $argument->setComponentNamespace($this->componentNamespace);
            }

            // Provide rendering context to certain data structures
            if ($argument instanceof RenderingContextAware) {
                $argument->setRenderingContext($renderingContext);
            }

            $variableContainer->add((string) $name, $argument);
        }

        // Initialize component rendering template
        if (!isset($this->parsedTemplate)) {
            $componentFile = $this->componentLoader->findComponent($this->componentNamespace);

            $this->parsedTemplate = $renderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
                $this->getTemplateIdentifier($componentFile),
                fn() => file_get_contents($componentFile)
            );
        }

        // Render component
        return $this->parsedTemplate->render($renderingContext);
    }

    protected function renderSlot(string $name)
    {
        $slot = $this->arguments[$name] ?? null;

        // Shortcut if template is rendered from cache
        // or parameter was provided directly to the component
        if (isset($slot) && !$slot instanceof RequiredSlotPlaceholder) {
            return $slot;
        }

        // Use content specified by <fc:content /> ViewHelpers
        // This is only executed for uncached templates
        if ($this->viewHelperNode !== null) {
            $contentViewHelpers = $this->extractContentViewHelpers($this->viewHelperNode, $this->renderingContext);
            if (isset($contentViewHelpers[$name])) {
                return (string) $contentViewHelpers[$name]->evaluateChildNodes($this->renderingContext);
            }
        }

        // Use tag content for default slot
        if ($name === self::DEFAULT_SLOT) {
            return (string) $this->renderChildren();
        }

        // Required Slot parameters are checked here for existence at last
        if ($slot instanceof RequiredSlotPlaceholder) {
            throw new InvalidArgumentException(sprintf(
                'Slot "%s" is required by component "%s", but no value was given.',
                $name,
                $this->componentNamespace
            ), 1681728555);
        }

        return $slot;
    }

    /**
     * Overwrites original compilation to store component namespace in compiled templates.
     *
     * @param string           $argumentsName
     * @param string           $closureName
     * @param string           $initializationPhpCode
     * @param ViewHelperNode   $node
     * @param TemplateCompiler $compiler
     *
     * @return string
     */
    public function compile(
        $argumentsName,
        $closureName,
        &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ): string {
        $allowedSlots = [];
        foreach ($node->getArgumentDefinitions() as $definition) {
            if (is_a($definition->getType(), Slot::class, true)) {
                $allowedSlots[$definition->getName()] = true;
            }
        }

        $contentViewHelpers = $this->extractContentViewHelpers($node, $compiler->getRenderingContext());
        foreach ($contentViewHelpers as $slotName => $viewHelperNode) {
            if (!isset($allowedSlots[$slotName])) {
                throw new InvalidArgumentException(sprintf(
                    'Slot "%s" does not exist in component "%s", but was used as named slot.',
                    $slotName,
                    $this->componentNamespace
                ), 1681832624);
            }

            $childNodesAsClosure = $compiler->wrapChildNodesInClosure($viewHelperNode);
            $initializationPhpCode .= sprintf('%s[\'%s\'] = %s;', $argumentsName, $slotName, $childNodesAsClosure) . chr(10);
        }

        return sprintf(
            '%s::renderComponent(%s, %s, $renderingContext, %s)',
            static::class,
            $argumentsName,
            $closureName,
            var_export($this->componentNamespace, true)
        );
    }

    /**
     * Replacement for renderStatic() to provide component namespace to ViewHelper.
     */
    public static function renderComponent(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
        string $componentNamespace
    ): mixed {
        $container = GeneralUtility::makeInstance(ContainerInterface::class);
        $componentRenderer = $container->get(static::class);
        $componentRenderer->setComponentNamespace($componentNamespace);

        return $renderingContext->getViewHelperInvoker()->invoke(
            $componentRenderer,
            $arguments,
            $renderingContext,
            $renderChildrenClosure
        );
    }

    /**
     * Initializes the component arguments based on the component definition.
     *
     * @throws Exception
     */
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'class',
            'string',
            'Additional CSS classes for the component'
        );
        $this->registerArgument(
            self::DEFAULT_SLOT,
            Slot::class,
            'Main content of the component; falls back to ViewHelper tag content',
            false,
            null,
            true
        );

        $this->initializeComponentParams();
    }

    /**
     * Initialize all arguments and return them.
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
     */
    public function validateAdditionalArguments(array $arguments): void
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
     * @throws InvalidArgumentException
     */
    public function validateArguments(): void
    {
        $argumentDefinitions = $this->prepareArguments();
        foreach ($argumentDefinitions as $argumentName => $registeredArgument) {
            if ($this->hasArgument($argumentName)) {
                $value = $this->arguments[$argumentName];
                $defaultValue = $registeredArgument->getDefaultValue();
                $type = $registeredArgument->getType();
                if ($value !== $defaultValue && $type !== 'mixed') {
                    $givenType = is_object($value) ? $value::class : gettype($value);
                    if (!$this->isValidType($type, $value)
                        && !$this->componentArgumentConverter->canTypeBeConvertedToType($givenType, $type)
                    ) {
                        throw new InvalidArgumentException(
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
     * Creates ViewHelper arguments based on the params defined in the component definition.
     */
    protected function initializeComponentParams(): void
    {
        $renderingContext = $this->getRenderingContext();

        $componentFile = $this->componentLoader->findComponent($this->componentNamespace);

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
                    $param['default'] = implode('', array_map(fn($node) => $node->evaluate($renderingContext), $paramNode->getChildNodes()));
                    $param['default'] = $param['default'] === '' ? null : $param['default'];
                }

                if (in_array($param['name'], $this->reservedArguments)) {
                    throw new Exception(sprintf(
                        'The argument "%s" defined in "%s" cannot be used because it is reserved.',
                        $param['name'],
                        $this->getComponentNamespace()
                    ), 1532960145);
                }

                // Resolve type aliases
                $param['type'] = $this->componentArgumentConverter->resolveTypeAlias($param['type']);

                // Enforce boolean node, see implementation in ViewHelperNode::rewriteBooleanNodesInArgumentsObjectTree()
                if ($param['type'] === 'boolean' || $param['type'] === 'bool') {
                    $param['default'] = BooleanNode::convertToBoolean($param['default'], $renderingContext);
                // Make sure that default value for object parameters is either a valid object or null
                } elseif (class_exists($param['type']) &&
                    !$param['default'] instanceof $param['type'] &&
                    !$this->componentArgumentConverter->canTypeBeConvertedToType(gettype($param['default']), $param['type'])
                ) {
                    $param['default'] = null;
                }

                $optional = $param['optional'] ?? false;
                $description = $param['description'] ?? '';
                $escape = is_subclass_of($param['type'], EscapedParameter::class) ? true : null;

                // Special handling for required Slot parameters
                // This is necessary to be able to use <fc:content /> instead of a component parameter because
                // the Fluid parser checks for existing arguments early in the parsing process
                if (is_a($param['type'], Slot::class, true) && !$optional) {
                    $optional = true;
                    $param['default'] = new RequiredSlotPlaceholder;
                }

                $this->registerArgument($param['name'], $param['type'], $description, !$optional, $param['default'], $escape);
            }
        }
    }

    /**
     * Extracts all <fc:content /> ViewHelpers from Fluid template node.
     */
    protected function extractContentViewHelpers(NodeInterface $node, RenderingContextInterface $renderingContext): array
    {
        return array_reduce(
            $this->extractViewHelpers($node, ContentViewHelper::class),
            function (array $nodes, ViewHelperNode $node) use ($renderingContext) {
                $slotArgument = $node->getArguments()['slot'] ?? null;
                $slotName = ($slotArgument) ? $slotArgument->evaluate($renderingContext) : self::DEFAULT_SLOT;
                $nodes[$slotName] = $node;
                return $nodes;
            },
            []
        );
    }

    /**
     * Extract all ViewHelpers of a certain type from a Fluid template node.
     */
    protected function extractViewHelpers(NodeInterface $node, string $viewHelperClassName): array
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
     * Returns an identifier by which fluid templates will be stored in the cache.
     */
    protected function getTemplateIdentifier(string $pathAndFilename, string $prefix = 'fluidcomponent'): string
    {
        $templateModifiedTimestamp = $pathAndFilename !== 'php://stdin' && file_exists($pathAndFilename) ? filemtime($pathAndFilename) : 0;
        return sprintf(
            '%s_%s_%s',
            $prefix,
            substr(strrchr($this->componentNamespace, "\\"), 1),
            sha1($pathAndFilename . '|' . $templateModifiedTimestamp)
        );
    }

    /**
     * Returns the prefixer object responsible for the current component namespaces.
     */
    protected function getComponentPrefixer(): ComponentPrefixerInterface
    {
        if (!isset(self::$componentPrefixerCache[$this->componentNamespace])) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']) &&
                is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer'])
            ) {
                arsort($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer']);
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['prefixer'] as $namespace => $prefixer) {
                    $namespace = ltrim($namespace, '\\');
                    if (str_starts_with($this->componentNamespace, $namespace)) {
                        $componentPrefixerClass = $prefixer;
                        break;
                    }
                }
            }

            if (empty($componentPrefixerClass)) {
                $componentPrefixerClass = GenericComponentPrefixer::class;
            }

            if ($this->container->has($componentPrefixerClass)) {
                $componentPrefixer = $this->container->get($componentPrefixerClass);
            } else {
                $componentPrefixer = GeneralUtility::makeInstance($componentPrefixerClass);
            }

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

    protected function getRenderingContext(): RenderingContext
    {
        if ($this->container->has(RenderingContextFactory::class)) {
            return $this->container->get(RenderingContextFactory::class)->create();
        } else {
            return GeneralUtility::makeInstance(RenderingContext::class);
        }
    }

    protected static function shouldUseTemplatePaths(): bool
    {
        static $assertion = null;
        if ($assertion === null) {
            $assertion = GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('fluidComponents.partialsInComponents');
        }
        return $assertion;
    }
}
