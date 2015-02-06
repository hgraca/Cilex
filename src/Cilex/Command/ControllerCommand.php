<?php
namespace Cilex\Command;

use Cilex\Command\Concept\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to generate a new code unit
 */
class ControllerCommand extends CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('controller')
            ->setDescription(
                'Generates a controller for a model'
            )
            ->addOption('component', 'c', InputOption::VALUE_REQUIRED, 'If set, the component name.')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'If set, the model name.')
            ->addOption('uri', 'u', InputOption::VALUE_REQUIRED, 'If set, the uri for this controller.')
            ->addOption(
                'project',
                'p',
                InputOption::VALUE_REQUIRED,
                'If set, the project for this controller (admin, manage, staff, ...).'
            )
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'If set, existing files will be overwritten.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentName = $input->getOption('component');
        $modelName     = $input->getOption('model');
        $uri           = $input->getOption('uri');
        $project       = $input->getOption('project');
        $overwrite     = $input->getOption('overwrite');

        $this->createController($input, $output, $componentName, $modelName, $uri, $project, $overwrite);
    }
}
