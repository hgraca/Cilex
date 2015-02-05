<?php
namespace Cilex\Service;

use Cilex\Service\Concept\ServiceAbstract;

class ServiceCreatorService extends ServiceAbstract
{

    /**
     * @param string          $componentName
     * @param string          $modelClassName
     * @param bool            $overwrite
     */
    public function create(
        $componentName = null,
        $modelClassName = null,
        $overwrite = false
    ) {

        $componentPath = $this->getComponentPath($componentName);

        if (false === file_exists($componentPath))
        {
            $this->logger->error("The component '{$componentName}' doesn't exist. You need to create it first.");

            exit;
        }

        $componentServicesPath = $this->getComponentServicesPath($componentName);
        $serviceFilePath       = $this->getServiceFilePath($componentName, $modelClassName);

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

        // create the service
        if (file_exists($serviceFilePath) && (false === $overwrite))
        {
            $this->logger->notice("The service '{$modelClassName}Service' already exists and will not be overwritten.");

            return;
        }
        else
        {
            $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_SERVICE);
            $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
            file_put_contents($serviceFilePath, $template);
            exec("git add $serviceFilePath");
        }

        // add the service to the service manager
        $this->addServiceToManager($componentName, $modelClassName);
    }

    /**
     * @param $componentName
     * @param $modelClassName
     */
    private function addServiceToManager($componentName, $modelClassName)
    {
        $serviceManagerFilePath = $this->getServiceManagerPath($componentName);
        $template               = file_get_contents($serviceManagerFilePath);

        // add the use for the new service
        $find     = file_get_contents($this->getTemplatesPath() . "/ServiceManager_use_from.php.tmpl");
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_use_to.php.tmpl");
        $template = str_replace($find, $replace, $template);

        // add a property
        $find     = '{';
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_property_to.php.tmpl");
        $pos      = strpos($template, $find);
        $template = substr_replace($template, $replace, $pos, 1); // add comma in last parameter

        // add a parameter to the docblock
        $find     = file_get_contents($this->getTemplatesPath() . "/ServiceManager_constr_inj_doc_from.php.tmpl");
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_constr_inj_doc_to.php.tmpl");
        $template = str_replace($find, $replace, $template);

        // add constructor initialization
        $find     = '    ) {';
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_constr_inj.php.tmpl");
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
        $replace  = file_get_contents($this->getTemplatesPath() . "/ServiceManager_getter.php.tmpl");
        $template = $this->str_lreplace($find, $replace, $template);

        // replace stubs
        $modelVarName = lcfirst($modelClassName);
        $template     = str_replace(self::REPLACEMENT_STRING_MODEL_VAR_NAME, $modelVarName, $template);
        $template     = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);

        file_put_contents($serviceManagerFilePath, $template);
    }

}
