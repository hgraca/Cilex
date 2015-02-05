<?php
namespace Cilex\Command;

use Cilex\Command\Concept\CommandAbstract;
use Cilex\Service\ComponentCreatorService;
use Cilex\Service\LoggerService;
use Cilex\Service\ServiceCreatorService;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to generate a new code unit
 */
class ComponentCommand extends CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('component')
            ->setDescription('Generates a full or partial component, with service, repository, controller, paginator, ...')
            ->addOption('component', 'c', InputOption::VALUE_REQUIRED, 'If set, the component name.')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'If set, existing files will be overwritten.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentName    = $input->getOption('component');
        $overwrite        = $input->getOption('overwrite');

        $this->createComponent($input, $output, $componentName, $overwrite);
    }
}
