<?php

namespace Cilex\Service;

use Cilex\Service\Concept\ServiceAbstract;

class ComponentCreatorService extends ServiceAbstract
{

    public function create(
        $componentName,
        $overwrite = false
    ) {

        $componentPath              = $this->getComponentPath($componentName);
        $componentExceptionsPath    = $this->getComponentExceptionsPath($componentName);
        $componentServicesPath      = $this->getComponentServicesPath($componentName);
        $serviceManagerFilePath     = $this->getServiceManagerPath($componentName);
        $componentExceptionFilePath = $this->getComponentExceptionFilePath($componentName);

        if (false === file_exists($componentPath))
        {
            if (mkdir($componentPath, 0777, true))
            {
                $this->logger->info("Component folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component folder.");
                exit;
            }

        }

        if (false === file_exists($componentExceptionsPath))
        {
            if (mkdir($componentExceptionsPath, 0777, true))
            {
                $this->logger->info("Component exceptions folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component exceptions folder.");
                exit;
            }
        }

        if (false === file_exists($componentServicesPath))
        {
            if (mkdir($componentServicesPath, 0777, true))
            {
                $this->logger->info("Component services folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component services folder.");
                exit;
            }
        }

        // create the exception
        if (file_exists($componentExceptionFilePath) && (false === $overwrite))
        {
            $this->logger->notice("Component exception already exists and will not be overwritten.");
        }
        else
        {
            $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_EXCEPTION);
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
            file_put_contents($componentExceptionFilePath, $template);
            exec("git add $componentExceptionFilePath");
        }

        // create the service manager
        if (file_exists($serviceManagerFilePath) && (false === $overwrite))
        {
            $this->logger->notice("Component service manager already exists and will not be overwritten.");
        }
        else
        {
            $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_SERVICE_MANAGER);
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
            file_put_contents($serviceManagerFilePath, $template);
            exec("git add $serviceManagerFilePath");
        }
    }
}
