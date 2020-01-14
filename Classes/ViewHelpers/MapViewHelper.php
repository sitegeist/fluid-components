<?php

namespace SMS\FluidComponents\ViewHelpers;

use SMS\FluidComponents\Fluid\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

class MapViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;
    protected $escapeChildren = false;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        $this->registerArgument('map', 'mixed', 'Structure of the output array', true);
        $this->registerArgument('each', 'array', '', false);
        $this->registerArgument('as', 'string', '', false);
        $this->registerArgument('useExpression', 'boolean', '', false, false);
    }

    /*
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        if ($arguments['each']) {
            $mapped = [];
            foreach ($arguments['each'] as $key => $item) {
                $templateVariableContainer = new StandardVariableProvider($item);
                if ($arguments['useExpression']) {
                    $mapped[$key] = static::mapExpressions($arguments['map'], $templateVariableContainer);
                } else {
                    $mapped[$key] = static::mapVariables($arguments['map'], $templateVariableContainer);
                }
            }
        } else {
            $templateVariableContainer = $renderingContext->getVariableProvider();
            if ($arguments['useExpression']) {
                $mapped = static::mapExpressions($arguments['map'], $templateVariableContainer);
            } else {
                $mapped = static::mapVariables($arguments['map'], $templateVariableContainer);
            }
        }

        $templateVariableContainer = $renderingContext->getVariableProvider();
        if (isset($arguments['as'])) {
            $templateVariableContainer->add($arguments['as'], $mapped);
            $output = $renderChildrenClosure();
            $templateVariableContainer->remove($arguments['as']);
            return $output;
        } else {
            return $mapped;
        }
    }

    public static function mapExpressions($map, VariableProviderInterface $variableProvider)
    {
        $expressionLanguage = new ExpressionLanguage();
        return static::applyMapping(
            $map,
            function ($expression) use ($variableProvider, $expressionLanguage) {
                return $expressionLanguage->evaluate($map, $variableProvider->getAll());
            }
        );
    }

    public static function mapVariables($map, VariableProviderInterface $variableProvider)
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $renderingContext->setVariableProvider($variableProvider);

        $templateParser = $renderingContext->getTemplateParser();
        return static::applyMapping(
            $map,
            function ($expression) use ($renderingContext, $templateParser) {
                return $templateParser->parse('{escapingEnabled=off}{' . $expression . '}')->render($renderingContext);
            }
        );
    }

    public static function applyMapping($map, callable $callback)
    {
        if (is_array($map)) {
            return array_map(function ($expression) use ($callback) {
                return static::applyMapping($expression, $callback);
            }, $map);
        } else {
            $expression = $map;
            return $callback($expression);
        }
    }
}
