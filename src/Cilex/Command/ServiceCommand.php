<?php
namespace Cilex\Command;

use Cilex\Command\Concept\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to generate a new code unit
 */
class ServiceCommand extends CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('service')
            ->setDescription(
                'Generates a full or partial service, with repository, ...'
            )
            ->addOption('component', 'c', InputOption::VALUE_REQUIRED, 'If set, the component name.')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'If set, the model name.')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'If set, existing files will be overwritten.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentName = $input->getOption('component');
        $modelName     = $input->getOption('model');
        $overwrite     = $input->getOption('overwrite');

        $this->createService($input, $output, $componentName, $modelName, $overwrite);
    }
}
