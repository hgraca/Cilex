<?php
namespace Cilex\Service;

use Cilex\Service\Concept\ServiceAbstract;

class ControllerCreatorService extends ServiceAbstract
{
    const TEMPLATE_PATH                      = "/%s.php.tmpl";
    const REPLACEMENT_STRING_URI             = "<URI>";
    const REPLACEMENT_STRING_CONTROLLER_NAME = "<CONTROLLER_NAME>";

    /**
     * @param string $componentName
     * @param        $modelName
     * @param        $uri
     * @param        $project
     * @param bool   $overwrite
     */
    public function create(
        $componentName,
        $modelName,
        $uri,
        $project,
        $overwrite
    ) {
        $controllerName = $this->uriToControllerName($uri);

        $componentPath = $this->getComponentPath($componentName);

        if (false === file_exists($componentPath))
        {
            $this->logger->error("The component '{$componentName}' doesn't exist. You need to create it first.");

            exit;
        }

        $componentControllersPath = $this->getControllersPath($componentName, $project);
        $controllerFilePath       = $this->getControllerFilePath($componentName, $project, $controllerName);

        if (false === file_exists($componentControllersPath))
        {
            if (mkdir($componentControllersPath, 0777, true))
            {
                $this->logger->info("Component controllers folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component controllers folder.");
                exit;
            }
        }

        // create the service
        if (file_exists($controllerFilePath) && (false === $overwrite))
        {
            $this->logger->notice(
                "The controller '{$controllerName}' already exists and will not be overwritten."
            );

            return;
        }
        else
        {
            $template = file_get_contents($this->getTemplatesPath() . $this->getTemplateName($project));
            $template = str_replace(self::REPLACEMENT_STRING_URI, $uri, $template);
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT_VAR, lcfirst($componentName), $template);
            $template = str_replace(self::REPLACEMENT_STRING_CONTROLLER_NAME, $controllerName, $template);
            $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelName, $template);
            $template = str_replace(self::REPLACEMENT_STRING_MODEL_VAR_NAME, lcfirst($modelName), $template);
            file_put_contents($controllerFilePath, $template);
            exec("git add $controllerFilePath");
        }
    }

    protected function uriToControllerName($uri)
    {
        return $this->studlyCase($uri);
    }

    protected function getControllersPath($componentName, $project)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/controllers/$project";
    }

    protected function getControllerFilePath($componentName, $project, $controllerName)
    {
        $rootPath = $this->getProjectRootPath();

        $controllerSuffix = $this->getControllerSuffix($project);

        return $rootPath . "/app/controllers/{$project}/{$controllerName}{$controllerSuffix}.php";
    }

    protected function getControllerSuffix($project)
    {
        return ucfirst($project) . 'Controller';
    }

    protected function getTemplateName($project)
    {
        $controllerSuffix = $this->getControllerSuffix($project);

        return sprintf(self::TEMPLATE_PATH, $controllerSuffix);
    }
}
