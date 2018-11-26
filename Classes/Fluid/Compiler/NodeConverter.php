<?php

namespace SMS\FluidComponents\Fluid\Compiler;

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * Class NodeConverter
 */
class NodeConverter extends \TYPO3Fluid\Fluid\Core\Compiler\NodeConverter
{
    /**
     * Converts the default value of an argument to cacheable PHP code
     *
     * @param mixed $defaultValue
     * @return string
     */
    protected function convertArgumentDefaultValue($defaultValue)
    {
        if ($defaultValue instanceof NodeInterface) {
            return $this->wrapChildNodesInClosure($defaultValue);
        } else {
            return var_export($defaultValue, true);
        }
    }

    /**
     * Generates PHP code of a closure that represents the default value of an argument
     *
     * @param NodeInterface $node
     * @return string
     */
    public function wrapChildNodesInClosure(NodeInterface $node)
    {
        $closure = '';
        $closure .= 'function(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) use ($self) {' . chr(10);
        $convertedNode = $this->convert($node);
        $closure .= $convertedNode['initialization'];
        $closure .= sprintf('return %s;', $convertedNode['execution']) . chr(10);
        $closure .= '}';
        return $closure;
    }

    /**
     * Convert a single ViewHelperNode into its cached representation. If the ViewHelper implements the "Compilable" facet,
     * the ViewHelper itself is asked for its cached PHP code representation. If not, a ViewHelper is built and then invoked.
     *
     * @param ViewHelperNode $node
     * @return array
     * @see convert()
     */
    protected function convertViewHelperNode(ViewHelperNode $node)
    {
        $initializationPhpCode = '// Rendering ViewHelper ' . $node->getViewHelperClassName() . chr(10);

        // Build up $arguments array
        $argumentsVariableName = $this->variableName('arguments');
        $renderChildrenClosureVariableName = $this->variableName('renderChildrenClosure');
        $viewHelperInitializationPhpCode = '';

        try {
            $convertedViewHelperExecutionCode = $node->getUninitializedViewHelper()->compile(
                $argumentsVariableName,
                $renderChildrenClosureVariableName,
                $viewHelperInitializationPhpCode,
                $node,
                $this->templateCompiler
            );

            $arguments = $node->getArgumentDefinitions();
            $argumentInitializationCode = sprintf('%s = array();', $argumentsVariableName) . chr(10);
            foreach ($arguments as $argumentName => $argumentDefinition) {
                if (!isset($alreadyBuiltArguments[$argumentName])) {
                    $argumentInitializationCode .= sprintf(
                        '%s[\'%s\'] = %s;%s',
                        $argumentsVariableName,
                        $argumentName,
                        // This line changed!
                        $this->convertArgumentDefaultValue($argumentDefinition->getDefaultValue()),
                        chr(10)
                    );
                }
            }

            $alreadyBuiltArguments = [];
            foreach ($node->getArguments() as $argumentName => $argumentValue) {
                if ($argumentValue instanceof NodeInterface) {
                    $converted = $this->convert($argumentValue);
                } else {
                    $converted = [
                        'initialization' => '',
                        'execution' => $argumentValue
                    ];
                }
                $argumentInitializationCode .= $converted['initialization'];
                $argumentInitializationCode .= sprintf(
                    '%s[\'%s\'] = %s;',
                    $argumentsVariableName,
                    $argumentName,
                    $converted['execution']
                ) . chr(10);
                $alreadyBuiltArguments[$argumentName] = true;
            }

            // Build up closure which renders the child nodes
            $initializationPhpCode .= sprintf(
                '%s = %s;',
                $renderChildrenClosureVariableName,
                $this->templateCompiler->wrapChildNodesInClosure($node)
            ) . chr(10);

            $initializationPhpCode .= $argumentInitializationCode . $viewHelperInitializationPhpCode;
        } catch (StopCompilingChildrenException $stopCompilingChildrenException) {
            $convertedViewHelperExecutionCode = '\'' . $stopCompilingChildrenException->getReplacementString() . '\'';
        }
        $initializationArray = [
            'initialization' => $initializationPhpCode,
            'execution' => $convertedViewHelperExecutionCode === null ? 'NULL' : $convertedViewHelperExecutionCode
        ];
        return $initializationArray;
    }
}
