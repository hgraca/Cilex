<?php
namespace Cilex\Service;

use Cilex\Service\Concept\ServiceAbstract;

class PaginatorCreatorService extends ServiceAbstract
{
    const TEMPLATE_PATH_PAGINATOR = "/Paginator.php.tmpl";
    const REPLACEMENT_STRING_FILTER = "<FILTER_PROPERTY>";

    /**
     * @param string $componentName
     * @param string $modelClassName
     * @param null   $filterProperty
     * @param bool   $overwrite
     */
    public function create(
        $componentName = null,
        $modelClassName = null,
        $filterProperty = null,
        $overwrite = false
    ) {

        $componentPath = $this->getComponentPath($componentName);

        if (false === file_exists($componentPath))
        {
            $this->logger->error("The component '{$componentName}' doesn't exist. You need to create it first.");

            exit;
        }

        $componentPaginatorsPath = $this->getPaginatorsPath($componentName);
        $paginatorFilePath       = $this->getPaginatorFilePath($componentName, $modelClassName);

        if (false === file_exists($componentPaginatorsPath))
        {
            if (mkdir($componentPaginatorsPath, 0777, true))
            {
                $this->logger->info("Component paginator folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component services folder.");
                exit;
            }
        }

        // create the service
        if (file_exists($paginatorFilePath) && (false === $overwrite))
        {
            $this->logger->notice(
                "The service '{$modelClassName}Paginator' already exists and will not be overwritten."
            );

            return;
        }
        else
        {
            $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_PAGINATOR);
            $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);
            $template = str_replace(self::REPLACEMENT_STRING_FILTER, $filterProperty, $template);
            file_put_contents($paginatorFilePath, $template);
            exec("git add $paginatorFilePath");
        }
    }

    protected function getPaginatorsPath()
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Paginator";
    }

    protected function getPaginatorFilePath($modelClassName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Paginator/{$modelClassName}Repository.php";
    }
}
