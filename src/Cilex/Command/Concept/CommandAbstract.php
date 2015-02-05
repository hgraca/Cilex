<?php
namespace Cilex\Command\Concept;

use Cilex\Service\ComponentCreatorService;
use Cilex\Service\LoggerService;
use Cilex\Service\PaginatorCreatorService;
use Cilex\Service\RepositoryCreatorService;
use Cilex\Service\ServiceCreatorService;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Provider\Console\Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Command to generate a new code unit
 */
class CommandAbstract extends Command
{

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $componentName
     * @param                 $overwrite
     *
     * @throws \Exception
     *
     */
    protected function createComponent(InputInterface $input, OutputInterface $output, $componentName, $overwrite)
    {
        if (empty($componentName))
        {
            $componentName = $this->askComponentName($input, $output);
        }

        $componentCreator = new ComponentCreatorService(new LoggerService($output));
        $componentCreator->create($componentName, $overwrite);

        // create services
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new ConfirmationQuestion('Add a service? [(y)|n]', true);
        while ($questionHelper->ask($input, $output, $question))
        {
            $this->createService($input, $output, $componentName, null, $overwrite);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $componentName
     * @param                 $modelName
     * @param                 $overwrite
     *
     */
    protected function createService(
        InputInterface $input,
        OutputInterface $output,
        $componentName,
        $modelName,
        $overwrite
    ) {
        if (empty($componentName))
        {
            $componentName = $this->askComponentName($input, $output);
        }

        if (empty($modelClassName))
        {
            $modelName = $this->askModelName($input, $output);
        }

        $serviceCreator = new ServiceCreatorService(new LoggerService($output));
        $serviceCreator->create(
            $componentName,
            $modelName,
            $overwrite
        );

        // create the repository
        /** @var QuestionHelper $questionHelper */
        $questionHelper    = $this->getHelper('question');
        $question          = new ConfirmationQuestion('Add a repository? [(y)|n]', true);
        if ($questionHelper->ask($input, $output, $question))
        {
            $this->createRepository($input, $output, $componentName, $modelName, null, $overwrite);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $componentName
     * @param                 $modelName
     * @param                 $bound
     * @param                 $overwrite
     *
     * @throws \Exception
     */
    protected function createRepository(
        InputInterface $input,
        OutputInterface $output,
        $componentName,
        $modelName,
        $bound,
        $overwrite
    ) {
        if (empty($componentName))
        {
            $componentName = $this->askComponentName($input, $output);
        }

        if (empty($modelClassName))
        {
            $modelName = $this->askModelName($input, $output);
        }

        if (empty($bound))
        {
            $bound = $this->askModelbound($input, $output);
        }

        $repositoryCreator = new RepositoryCreatorService(new LoggerService($output));
        $repositoryCreator->create(
            $componentName,
            $modelName,
            $bound,
            $overwrite
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $componentName
     * @param                 $modelName
     * @param                 $filterProperty
     * @param                 $overwrite
     *
     * @throws \Exception
     */
    protected function createPaginator(
        InputInterface $input,
        OutputInterface $output,
        $componentName,
        $modelName,
        $filterProperty,
        $overwrite
    ) {
        if (empty($componentName))
        {
            $componentName = $this->askComponentName($input, $output);
        }

        if (empty($modelName))
        {
            $modelName = $this->askModelName($input, $output);
        }

        if (empty($filterProperty))
        {
            $filterProperty = $this->askFilterProperty($input, $output);
        }

        $paginatorCreator = new PaginatorCreatorService(new LoggerService($output));
        $paginatorCreator->create(
            $componentName,
            $modelName,
            $filterProperty,
            $overwrite
        );
    }


    protected function askComponentName(InputInterface $input, OutputInterface $output)
    {
        $componentName = $input->getOption('component');

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new Question('Please enter the name of the component: ');
        $i              = 0;
        while (empty($componentName) && ($i < 3))
        {
            $componentName = $questionHelper->ask($input, $output, $question);
            $i++;
        }

        If (empty($componentName))
        {
            throw new \Exception("Can not proceed without a component name.");
        }

        return $componentName;
    }

    protected function askModelName(InputInterface $input, OutputInterface $output)
    {
        $modelName = $input->getOption('model');

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new Question('Please enter the name of the model: ');
        $i              = 0;
        while (empty($modelName) && ($i < 3))
        {
            $modelName = $questionHelper->ask($input, $output, $question);
            $i++;
        }

        If (empty($modelName))
        {
            throw new \Exception("Can not proceed without a model name.");
        }

        return $modelName;
    }

    protected function askFilterProperty(InputInterface $input, OutputInterface $output)
    {
        $filterProperty = $input->getOption('filter');

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new Question('Please enter the name of the model property used to filter: ');
        $i              = 0;
        while (empty($filterProperty) && ($i < 3))
        {
            $filterProperty = $questionHelper->ask($input, $output, $question);
            $i++;
        }

        If (empty($filterProperty))
        {
            throw new \Exception("Can not proceed without a model property used to filter.");
        }

        return $filterProperty;
    }

    protected function askModelBound(InputInterface $input, OutputInterface $output)
    {
        $bound = null;

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new ConfirmationQuestion('Is the model bound to a shop? [y|n] ', null);
        $bound          = $questionHelper->ask($input, $output, $question);

        return $bound;
    }
}
