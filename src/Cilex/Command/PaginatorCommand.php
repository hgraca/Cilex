<?php
namespace Cilex\Command;

use Cilex\Command\Concept\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to generate a new code unit
 */
class PaginatorCommand extends CommandAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('paginator')
            ->setDescription(
                'Generates a paginator for a model'
            )
            ->addOption('component', 'c', InputOption::VALUE_REQUIRED, 'If set, the component name.')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'If set, the model name.')
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'If set, the model property used to filter.')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'If set, existing files will be overwritten.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentName  = $input->getOption('component');
        $modelName      = $input->getOption('model');
        $filterProperty = $input->getOption('filter');
        $overwrite      = $input->getOption('overwrite');

        $this->createPaginator($input, $output, $componentName, $modelName, $filterProperty, $overwrite);
    }
}
