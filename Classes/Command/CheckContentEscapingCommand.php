<?php
declare(strict_types=1);

namespace SMS\FluidComponents\Command;

use IteratorAggregate;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use SMS\FluidComponents\Utility\ComponentLoader;
use SMS\FluidComponents\ViewHelpers\SlotViewHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\ViewHelpers\Format\HtmlViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;

class CheckContentEscapingCommand extends Command
{
    /**
     * List of ViewHelpers that are usually used to un-escape variables
     * that are passed as content to a component
     */
    const RAW_VIEWHELPERS = [
        RawViewHelper::class,
        HtmlViewHelper::class
    ];

    /**
     * Variables that don't contain any HTML and thus don't need to be
     * checked
     */
    const IGNORED_VARIABLES = [
        'component.prefix',
        'component.class'
    ];

    protected ?PackageManager $packageManager;
    protected ?ComponentLoader $componentLoader;

    protected array $templates = [];
    protected array $results = [];
    protected array $affectedComponents = [];

    protected function configure()
    {
        $this->setDescription(
            'Checks for possible escaping issues with content parameter due to new children escaping behavior'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $componentNamespaces = $this->componentLoader->getNamespaces();
        $templateFiles = $this->discoverTemplateFiles();

        $progress = new ProgressBar($output, count($componentNamespaces) + count($templateFiles));
        $progress->start();

        // Determine which components use {content -> f:format.raw()} or similar
        foreach ($componentNamespaces as $namespace => $path) {
            foreach ($this->componentLoader->findComponentsInNamespace($namespace) as $className => $file) {
                try {
                    $template = $this->parseTemplate($file);
                } catch (\TYPO3Fluid\Fluid\Core\Parser\Exception $e) {
                    $this->addResult($file, $e->getMessage(), true);
                    continue;
                }

                if ($this->detectRawContentVariable($template->getRootNode())) {
                    $this->affectedComponents[$className] = $file;
                }
            }
            $progress->advance();
        }

        // Check all templates for usage of the content parameter in combination with variables
        foreach ($templateFiles as $file) {
            $file = (string)$file;
            try {
                $template = $this->parseTemplate($file);
            } catch (\TYPO3Fluid\Fluid\Core\Parser\Exception $e) {
                $this->addResult($file, $e->getMessage(), true);
                continue;
            }

            $results = $this->detectEscapedVariablesPassedAsContent($template->getRootNode());
            foreach ($results as $result) {
                $this->addResult($file, sprintf(
                    'Component "%s" expects raw html content, but was called with potentially escaped variables: %s',
                    $this->cleanupPathForOutput($result[0]),
                    implode(', ', array_map(function ($variableName) {
                        return '{' . $variableName . '}';
                    }, $result[1]))
                ));
            }
            $progress->advance();
        }

        $progress->finish();

        // Sort results alphabetically
        ksort($this->results);

        // Output results
        $output->writeln('');
        foreach ($this->results as $file => $messages) {
            $output->writeln('');
            $output->writeln(sprintf(
                '<fg=green;options=bold>%s:</>',
                $this->cleanupPathForOutput($file)
            ));
            $output->writeln('');

            foreach ($messages as $message) {
                $output->writeln($message);
                $output->writeln('');
            }
        }

        return 0;
    }

    public function detectRawContentVariable(NodeInterface $node, array $parents = []): bool
    {
        $node = $this->resolveEscapingNode($node);

        $lastParent = count($parents) - 1;
        foreach ($node->getChildNodes() as $childNode) {
            $childNode = $this->resolveEscapingNode($childNode);

            // Check all parent elements of content variable
            if ($childNode instanceof ObjectAccessorNode && $childNode->getObjectPath() === 'content') {
                for ($i = $lastParent; $i >= 0; $i--) {
                    // Skip all non-viewhelpers
                    if (!($parents[$i] instanceof ViewHelperNode)) {
                        continue;
                    }

                    // Check for f:format.raw
                    if ($parents[$i]->getUninitializedViewHelper() instanceof RawViewHelper) {
                        return true;
                    }
                }
            }

            // Check if the slot ViewHelper is present
            if ($childNode instanceof ViewHelperNode) {
                $viewHelper = $childNode->getUninitializedViewHelper();
                if ($viewHelper instanceof SlotViewHelper) {
                    return true;
                }
            }

            // Search for more occurances of content variable
            $result = $this->detectRawContentVariable($childNode, array_merge($parents, [$childNode]));
            if ($result) {
                return true;
            }
        }

        return false;
    }

    public function detectEscapedVariablesPassedAsContent(NodeInterface $node): array
    {
        $node = $this->resolveEscapingNode($node);

        $results = [];
        foreach ($node->getChildNodes() as $childNode) {
            $childNode = $this->resolveEscapingNode($childNode);

            // Check if a component was used
            if ($childNode instanceof ViewHelperNode) {
                $viewHelper = $childNode->getUninitializedViewHelper();
                if (
                    $viewHelper instanceof ComponentRenderer &&
                    isset($this->affectedComponents[$viewHelper->getComponentNamespace()])
                ) {
                    // Check if variables were used inside of content parameter
                    $contentNode = $childNode->getArguments()['content'] ?? $childNode;
                    $variableNames = $this->checkForVariablesWithoutRaw($contentNode);
                    if (!empty($variableNames)) {
                        $results[] = [
                            $this->affectedComponents[$viewHelper->getComponentNamespace()],
                            $variableNames
                        ];
                    }
                    continue;
                }
            }

            $results = array_merge(
                $results,
                $this->detectEscapedVariablesPassedAsContent($childNode)
            );
        }

        return $results;
    }

    public function checkForVariablesWithoutRaw(NodeInterface $node, array $parents = []): array
    {
        $node = $this->resolveEscapingNode($node);

        $variableNames = [];
        $lastParent = count($parents) - 1;
        foreach ($node->getChildNodes() as $childNode) {
            $childNode = $this->resolveEscapingNode($childNode);

            // Check all parent elements of variables
            if (
                $childNode instanceof ObjectAccessorNode &&
                !in_array($childNode->getObjectPath(), static::IGNORED_VARIABLES)
            ) {
                for ($i = $lastParent; $i >= 0; $i--) {
                    // Skip all non-viewhelpers
                    if (!$parents[$i] instanceof ViewHelperNode) {
                        continue;
                    }

                    // Check for f:format.raw etc.
                    $viewHelper = $parents[$i]->getUninitializedViewHelper();
                    if (in_array(get_class($viewHelper), static::RAW_VIEWHELPERS)) {
                        continue 2;
                    }
                }

                $variableNames[] = $childNode->getObjectPath();
                continue;
            }

            // Search for more occurances of variables
            $variableNames = array_merge(
                $variableNames,
                $this->checkForVariablesWithoutRaw($childNode, array_merge($parents, [$childNode]))
            );
        }

        return $variableNames;
    }

    protected function discoverTemplateFiles(): array
    {
        // All extensions in local extension directory
        $activeExtensions = array_filter($this->packageManager->getActivePackages(), function ($package) {
            return strpos($package->getPackagePath(), Environment::getExtensionsPath()) === 0;
        });

        // All template paths (Resources/Private/)
        $possibleTemplatePaths = array_map(function ($package) {
            return ExtensionManagementUtility::extPath($package->getPackageKey(), 'Resources/Private/');
        }, $activeExtensions);
        $possibleTemplatePaths = array_filter($possibleTemplatePaths, 'file_exists');

        // Find all html files
        $finder = new Finder();
        $finder
            ->in($possibleTemplatePaths)
            ->files()->name('*.html');
        return iterator_to_array($finder);
    }

    protected function addResult(string $file, string $message, bool $isError = false): void
    {
        $this->results[$file] ??= [];
        $format = ($isError) ? '<fg=red>%s</>' : '<fg=yellow>%s</>';
        $this->results[$file][] = sprintf($format, $message);
    }

    protected function cleanupPathForOutput(string $path): string
    {
        return trim(str_replace(Environment::getProjectPath(), '', $path), '/');
    }

    protected function resolveEscapingNode(NodeInterface $node): NodeInterface
    {
        return ($node instanceof EscapingNode) ? $this->resolveEscapingNode($node->getNode()) : $node;
    }

    protected function parseTemplate(string $file): ParsingState
    {
        $this->templates[$file] ??= $this->getTemplateParser()->parse(
            file_get_contents($file),
            $file
        );
        return $this->templates[$file];
    }

    protected function getTemplateParser(): TemplateParser
    {
        return (new StandaloneView())->getRenderingContext()->getTemplateParser();
    }

    public function injectPackageManager(PackageManager $packageManager): void
    {
        $this->packageManager = $packageManager;
    }

    public function injectComponentLoader(ComponentLoader $componentLoader): void
    {
        $this->componentLoader = $componentLoader;
    }
}
