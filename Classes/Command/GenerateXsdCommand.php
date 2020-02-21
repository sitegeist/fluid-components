<?php
namespace SMS\FluidComponents\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GenerateXsdCommand extends Command
{
    /**
     * @var \SMS\FluidComponents\Service\XsdGenerator
     */
    private $xsdGenerator;

    protected function configure()
    {
        $this->setDescription(
            'Generates a xsd file for all fluid-components'
        );
        $this->setHelp(
            'Generates a xsd file for all fluid-components for auto completion in your IDE'
        );
//        $this->addArgument(
//            'First Name',
//            InputArgument::OPTIONAL,
//            'My description for the argument First Name',
//            'The default value (if argument is optional)'
//		);
//		$this->addOption(
//            'nothing',
//            null,
//            InputOption::VALUE_NONE,
//            ' Do nothing'
//        );
        $this->xsdGenerator = GeneralUtility::makeInstance(\SMS\FluidComponents\Service\XsdGenerator::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xsd = $this->xsdGenerator->generateXsd();
        // Do nothing
        $output->writeln($xsd);
    }

}
