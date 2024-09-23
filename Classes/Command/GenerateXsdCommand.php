<?php declare(strict_types=1);

namespace SMS\FluidComponents\Command;

use Exception;
use SMS\FluidComponents\Service\XsdGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateXsdCommand extends Command
{
    public function __construct(private XsdGenerator $xsdGenerator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Generates xsd files for all fluid-components'
        );
        $this->setHelp(
            <<<'EOH'
                Generates Schema documentation (XSD) for your fluid components, preparing the
                file to be placed online and used by any XSD-aware editor.
                After creating the XSD file, reference it in your IDE and import the namespace
                in your Fluid template by adding the xmlns:* attribute(s):
                <code><html xmlns="http://www.w3.org/1999/xhtml" xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers" ...></code>
                EOH
        );
        $this->addArgument(
            'path',
            InputArgument::OPTIONAL,
            'Path where to store the xsd files',
            '.'
        );
        $this->addOption(
            'namespace',
            'nc',
            InputOption::VALUE_OPTIONAL,
            'Namespace to generate xsd for',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        if (substr((string) $path, 0, 1) !== DIRECTORY_SEPARATOR) {
            $path = realpath(getcwd() . DIRECTORY_SEPARATOR . $path);
        }
        if ($output->isVerbose()) {
            $output->writeln('Path: ' . $path);
        }
        if (!is_dir($path)) {
            throw new Exception('Directory \'' . $input->getArgument('path') . '\' does not exist.', 1582535395);
        }
        $xsdTargetNameSpaces = $this->xsdGenerator->generateXsd($path, $input->getOption('namespace'));
        if (count($xsdTargetNameSpaces) === 0) {
            $output->writeln('<error>Namespace(s) not found.</error>');
            return 1;
        } else {
            // add fluid component view helpers (only to complete the namespace xml declaration)
            $xsdTargetNameSpaces['fc'][] = 'http://typo3.org/ns/SMS/FluidComponents/ViewHelpers';
            if ($output->isVerbose()) {
                $xmlHeader = '<html ';
                foreach ($xsdTargetNameSpaces as $prefix => $targetNameSpacesForPrefix) {
                    foreach ($targetNameSpacesForPrefix as $targetNameSpaceForPrefix) {
                        $xmlHeader .= 'xmlns:' . $prefix . '="' . $targetNameSpaceForPrefix . '"' . "\n";
                    }
                }
                $xmlHeader .= 'data-namespace-typo3-fluid="true">';
                $output->writeln('Import the namespaces in your Fluid template by adding the xmlns:* attributes:');
                $output->writeln('<info>' . $xmlHeader . '</info>');
            }
            return 0;
        }
    }
}
