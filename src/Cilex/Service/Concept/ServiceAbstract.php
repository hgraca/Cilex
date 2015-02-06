<?php

namespace Cilex\Service\Concept;

use Psr\Log\LoggerInterface;

class ServiceAbstract
{
    const REPLACEMENT_STRING_MODEL_CLASS_NAME           = '<MODELCLASS>';
    const REPLACEMENT_STRING_MODEL_VAR_NAME             = '<MODELCLASSVAR>';
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
