<?php
namespace Cilex\Command;

use Cilex\Command\Concept\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to generate a new code unit
 */
class RepositoryCommand extends CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('repository')
            ->setDescription(
                'Generates a repository with its interface ...'
            )
            ->addOption('component', 'c', InputOption::VALUE_REQUIRED, 'If set, the component name.')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'If set, the model name.')
            ->addOption('bound', 'b', InputOption::VALUE_NONE, 'If set, the model is bound to a shop.')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'If set, existing files will be overwritten.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentName = $input->getOption('component');
        $modelName     = $input->getOption('model');
        $bound         = $input->getOption('bound');
        $overwrite     = $input->getOption('overwrite');

        $this->createRepository($input, $output, $componentName, $modelName, $bound, $overwrite);
    }
}
