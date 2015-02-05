<?php

namespace Cilex\Service\Concept;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ServiceAbstract
{
    const TEMPLATE_PATH_SERVICE_MANAGER      = "/ServiceManager.php.tmpl";
    const TEMPLATE_PATH_EXCEPTION            = "/ComponentException.php.tmpl";
    const TEMPLATE_PATH_SERVICE              = "/Service.php.tmpl";
    const TEMPLATE_PATH_REPOSITORY           = "/Repository.php.tmpl";
    const TEMPLATE_PATH_REPOSITORY_INTERFACE = "/RepositoryInterface.php.tmpl";

    const REPLACEMENT_STRING_MODEL_CLASS_NAME           = '<MODELCLASS>';
    const REPLACEMENT_STRING_MODEL_VAR_NAME             = '<MODELCLASSVAR>';
    const REPLACEMENT_STRING_MODEL_PARENT_USE_CLASSPATH = '<USEPARENT>';
    const REPLACEMENT_STRING_MODEL_PARENT_CLASS_NAME    = '<PARENTCLASS>';
    const REPLACEMENT_STRING_COMPONENT                  = '<COMPONENT>';

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    protected function getProjectRootPath()
    {
        return exec('git rev-parse --show-toplevel');
    }

    protected function getComponentPath($componentName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/lib/SEOshop/BusinessLogic/{$componentName}";
    }

    protected function getComponentServicesPath($componentName)
    {
        return $this->getComponentPath($componentName) . "/Service";
    }

    protected function getComponentExceptionsPath($componentName)
    {
        return $this->getComponentPath($componentName) . "/Exception";
    }

    protected function getComponentExceptionFilePath($componentName)
    {
        return $this->getComponentExceptionsPath($componentName) . "/{$componentName}Exception.php";
    }

    protected function getServiceManagerPath($componentName)
    {
        return $this->getComponentPath($componentName) . "/{$componentName}ServiceManager.php";
    }

    protected function getTemplatesPath()
    {
        return PATH_TEMPLATES;
    }

    /**
     * @param $componentName
     * @param $modelClassName
     *
     * @return string
     */
    protected function getServiceFilePath($componentName, $modelClassName)
    {
        $componentServicesPath = $this->getComponentServicesPath($componentName);
        $serviceFilePath       = "{$componentServicesPath}/{$modelClassName}Service.php";

        return $serviceFilePath;
    }

}
