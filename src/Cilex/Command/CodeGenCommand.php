<?php
namespace Cilex\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Provider\Console\Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Command to generate a new code unit
 */
class CodeGenCommand extends Command
{
    const TEMPLATE_PATH_SERVICE_MANAGER      = "/ServiceManager.php";
    const TEMPLATE_PATH_EXCEPTION            = "/ComponentException.php";
    const TEMPLATE_PATH_SERVICE              = "/Service.php";
    const TEMPLATE_PATH_REPOSITORY           = "/Repository.php";
    const TEMPLATE_PATH_REPOSITORY_INTERFACE = "/RepositoryInterface.php";

    const REPLACEMENT_STRING_MODEL_CLASS_NAME           = '<MODELCLASS>';
    const REPLACEMENT_STRING_MODEL_VAR_NAME             = '<MODELCLASSVAR>';
    const REPLACEMENT_STRING_MODEL_PARENT_USE_CLASSPATH = '<USEPARENT>';
    const REPLACEMENT_STRING_MODEL_PARENT_CLASS_NAME    = '<PARENTCLASS>';
    const REPLACEMENT_STRING_COMPONENT                  = '<COMPONENT>';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('codegen')
            ->setDescription('Generates a code unit (component, service, repository)')
            ->addArgument(
                'codeUnit',
                InputArgument::REQUIRED,
                'What type of code unit do you want to generate? (component, service, repository)'
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
        $codeUnit = $input->getArgument('codeUnit');

        switch ($codeUnit)
        {
            case "component":
                $this->createComponent(
                    $input,
                    $output,
                    $input->getOption('component'),
                    $input->getOption('overwrite')
                );
                break;
            case "service":
                $this->createService(
                    $input,
                    $output,
                    $input->getOption('component'),
                    $input->getOption('model'),
                    $input->getOption('overwrite')
                );
                break;
            case "repository":
                $this->createRepository(
                    $input,
                    $output,
                    $input->getOption('model'),
                    $input->getOption('overwrite')
                );
                break;
            default:
                throw new \Exception("The code unit you are trying to generate is unknown.");
                break;
        }
    }

    public function createComponent(
        InputInterface $input,
        OutputInterface $output,
        $componentName = null,
        $overwrite = false
    ) {
        if (empty($componentName))
        {
            $componentName = $this->askComponentName($input, $output);
        }

        $componentPath              = $this->getComponentPath($componentName);
        $componentExceptionsPath    = $this->getComponentExceptionsPath($componentName);
        $componentServicesPath      = $this->getComponentServicesPath($componentName);
        $serviceManagerFilePath     = $this->getServiceManagerPath($componentName);
        $componentExceptionFilePath = $this->getComponentExceptionFilePath($componentName);

        if (false === file_exists($componentPath))
        {
            mkdir($componentPath, 0777, true);
        }

        if (false === file_exists($componentExceptionsPath))
        {
            mkdir($componentExceptionsPath, 0777, true);
        }

        if (false === file_exists($componentServicesPath))
        {
            mkdir($componentServicesPath, 0777, true);
        }

        // create the exception
        $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_EXCEPTION);
        $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
        file_put_contents($componentExceptionFilePath, $template);
        exec("git add $componentExceptionFilePath");

        // create the service manager
        $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_SERVICE_MANAGER);
        $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
        file_put_contents($serviceManagerFilePath, $template);
        exec("git add $serviceManagerFilePath");

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
     * @param string          $componentName
     * @param string          $modelClassName
     * @param bool            $overwrite
     */
    public function createService(
        InputInterface $input,
        OutputInterface $output,
        $componentName = null,
        $modelClassName = null,
        $overwrite = false
    ) {
        if (empty($componentName))
        {
            $componentName = $this->askComponentName($input, $output);
        }
        if (empty($modelClassName))
        {
            $modelClassName = $this->askModelName($input, $output);
        }

        $componentPath = $this->getComponentPath($componentName);

        if (false === file_exists($componentPath))
        {
            $output->writeln(
                "The component '{$componentName}' doesn't exist. You need to create it first."
            );

            return;
        }

        $componentServicesPath = $this->getComponentServicesPath($componentName);
        $serviceFilePath       = "{$componentServicesPath}/{$modelClassName}Service.php"; // @todo move this to a method
        if ((false === $overwrite) && file_exists($serviceFilePath))
        {
            $output->writeln(
                "The service '{$modelClassName}Service' already exists and will not be overwritten."
            );

            return;
        }

        if (false === file_exists($componentServicesPath))
        {
            mkdir($componentServicesPath, 0777, true);
        }

        // create the service
        $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_SERVICE);
        $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);
        $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
        file_put_contents($serviceFilePath, $template);
        exec("git add $serviceFilePath");

        // add the service to the service manager
        $this->addServiceToManager($componentName, $modelClassName);

        // create the repository
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new ConfirmationQuestion('Add a repository? [(y)|n]', true);
        if ($questionHelper->ask($input, $output, $question))
        {
            $this->createRepository($input, $output, $modelClassName, $overwrite);
        }
    }

    /**
     * @param $componentName
     * @param $modelClassName
     */
    private function addServiceToManager($componentName, $modelClassName)
    {
        $serviceManagerFilePath = $this->getServiceManagerPath($componentName);

        // @todo add the use for the new service

        // add a property
        $template = file_get_contents($serviceManagerFilePath);
        $find     = '{';
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_property_to.php");
        $pos      = strpos($template, $find);
        $template = substr_replace($template, $replace, $pos, 1); // add comma in last parameter

        // add a parameter to the docblock
        $find     = file_get_contents($this->getTemplatesPath() . "/ServiceManager_constr_inj_doc_from.php");
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_constr_inj_doc_to.php");
        $template = str_replace($find, $replace, $template);

        // add constructor initialization
        $find     = '    ) {';
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_constr_inj.php");
        $pos      = strpos($template, $find);
        $template = substr_replace($template, ',', $pos - 1, 0); // add comma in last parameter
        $template = str_replace("__construct(,", "__construct(", $template);// remove comma from constructor line
        $template = substr_replace($template, $replace, $pos, strlen($find) + 1); // add inj
        $template = str_replace(',        ', ",\n        ", $template);// add line brks in constructor line
        $template = str_replace(
            "<MODELCLASSVAR>Service = $<MODELCLASSVAR>Service;\n\n",
            "<MODELCLASSVAR>Service = $<MODELCLASSVAR>Service;\n",
            $template
        );// remove extra line brks in constructor line

        // add getter
        $find     = '}';
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_getter.php");
        $template = $this->str_lreplace($find, $replace, $template);

        // replace stubs
        $modelVarName = lcfirst($modelClassName);
        $template     = str_replace(self::REPLACEMENT_STRING_MODEL_VAR_NAME, $modelVarName, $template);
        $template     = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);

        file_put_contents($serviceManagerFilePath, $template);
    }

    private function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $modelClassName
     * @param bool            $overwrite
     *
     * @throws \Exception
     */
    public function createRepository(
        InputInterface $input,
        OutputInterface $output,
        $modelClassName = null,
        $overwrite = false
    ) {
        if (empty($modelClassName))
        {
            $modelClassName = $this->askModelName($input, $output);
        }
        $bound = $this->askModelbound($input, $output);

        if (false === file_exists($this->getRepositoriesPath()))
        {
            mkdir($this->getRepositoriesPath(), 0777, true);
        }

        $repoPath = $this->getRepositoryFilePath($modelClassName);

        if ((false === $overwrite) && file_exists($repoPath))
        {
            $output->writeln(
                "The repository '{$modelClassName}Repository' already exists and will not be overwritten."
            );

            return;
        }

        $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_REPOSITORY);

        if ($bound)
        {
            $useParent       = 'SEOshop\\BusinessLogic\\Core\\<PARENTCLASS>';
            $parentClassName = 'ShopRepositoryAbstract';
        }
        else
        {
            $useParent       = 'SEOshop\\Backend\\BigD\\Runtime\\<PARENTCLASS>';
            $parentClassName = 'RepositoryAbstract';
        }

        $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);
        $template = str_replace(self::REPLACEMENT_STRING_MODEL_PARENT_USE_CLASSPATH, $useParent, $template);
        $template = str_replace(self::REPLACEMENT_STRING_MODEL_PARENT_CLASS_NAME, $parentClassName, $template);

        file_put_contents($repoPath, $template);
        exec("git add $repoPath");

        $this->createRepositoryInterface($output, $modelClassName, $overwrite);
    }

    /**
     * @param OutputInterface $output
     * @param string          $modelClassName
     * @param bool            $overwrite
     */
    public function createRepositoryInterface(OutputInterface $output, $modelClassName, $overwrite = false)
    {
        $repoInterfacePath = $this->getRepositoryInterfacePath($modelClassName);

        if (false === file_exists($this->getRepositoryInterfacesPath()))
        {
            mkdir($this->getRepositoryInterfacesPath(), 0777, true);
        }

        if ((false === $overwrite) && file_exists($repoInterfacePath))
        {
            $output->writeln(
                "The repository interface '{$modelClassName}RepositoryInterface' already exists and will not be overwritten."
            );

            return;
        }

        $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_REPOSITORY_INTERFACE);

        $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);

        file_put_contents($repoInterfacePath, $template);
        exec("git add $repoInterfacePath");
    }

    private function getProjectRootPath()
    {
        return exec('git rev-parse --show-toplevel');
    }

    private function getComponentPath($componentName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/lib/SEOshop/BusinessLogic/{$componentName}";
    }

    private function getComponentServicesPath($componentName)
    {
        return $this->getComponentPath($componentName) . "/Service";
    }

    private function getComponentExceptionsPath($componentName)
    {
        return $this->getComponentPath($componentName) . "/Exception";
    }

    private function getComponentExceptionFilePath($componentName)
    {
        return $this->getComponentExceptionsPath($componentName) . "/{$componentName}Exception.php";
    }

    private function getServiceManagerPath($componentName)
    {
        return $this->getComponentPath($componentName) . "/{$componentName}ServiceManager.php";
    }

    private function getRepositoriesPath()
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository";
    }

    private function getRepositoryFilePath($modelClassName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository/{$modelClassName}Repository.php";
    }

    private function getRepositoryInterfacesPath()
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository/Contract";
    }

    private function getRepositoryInterfacePath($modelClassName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository/Contract/{$modelClassName}RepositoryInterface.php";
    }

    private function getTemplatesPath()
    {
        return PATH_TEMPLATES;
    }

    private function askComponentName(InputInterface $input, OutputInterface $output)
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

    private function askModelName(InputInterface $input, OutputInterface $output)
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

    private function askModelBound(InputInterface $input, OutputInterface $output)
    {
        $bound = null;

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question       = new ConfirmationQuestion('Is the model bound to a shop? [y|n] ', null);
        $bound = $questionHelper->ask($input, $output, $question);

        return $bound;
    }
}
