<?php

namespace Cilex\Service;

use Cilex\Service\Concept\ServiceAbstract;

class RepositoryCreatorService extends ServiceAbstract
{
    const TEMPLATE_PATH_REPOSITORY           = "/Repository.php.tmpl";
    const TEMPLATE_PATH_REPOSITORY_INTERFACE = "/RepositoryInterface.php.tmpl";

    const REPLACEMENT_STRING_MODEL_PARENT_USE_CLASSPATH = '<USEPARENT>';
    const REPLACEMENT_STRING_MODEL_PARENT_CLASS_NAME    = '<PARENTCLASS>';

    /**
     * @param null   $componentName
     * @param string $modelClassName
     * @param bool   $bound
     * @param bool   $overwrite
     *
     */
    public function create(
        $componentName = null,
        $modelClassName = null,
        $bound = true,
        $overwrite = false
    ) {

        if (false === file_exists($this->getRepositoriesPath()))
        {
            if (mkdir($this->getRepositoriesPath(), 0777, true))
            {
                $this->logger->info("Component repositories folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component repositories folder.");
                exit;
            }
        }

        $repoPath = $this->getRepositoryFilePath($modelClassName);

        if (file_exists($repoPath) && (false === $overwrite))
        {
            $this->logger->notice(
                "The repository '{$modelClassName}Repository' already exists and will not be overwritten."
            );

            return;
        }
        else
        {
            $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_REPOSITORY);

            if ($bound)
            {
                $useParent       = 'SEOshop\\BusinessLogic\\Core\\Concept\\<PARENTCLASS>';
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
            $template = str_replace(self::REPLACEMENT_STRING_COMPONENT, $componentName, $template);

            file_put_contents($repoPath, $template);
            exec("git add $repoPath");
        }

        $this->createRepositoryInterface($modelClassName, $overwrite);
    }

    /**
     * @param string $modelClassName
     * @param bool   $overwrite
     */
    public function createRepositoryInterface($modelClassName, $overwrite = false)
    {
        $repoInterfacePath = $this->getRepositoryInterfacePath($modelClassName);

        if (false === file_exists($this->getRepositoryInterfacesPath()))
        {
            if (mkdir($this->getRepositoryInterfacesPath(), 0777, true))
            {
                $this->logger->info("Component repositories interfaces folder created.");
            }
            else
            {
                $this->logger->error("Could not create the component repositories interfaces folder.");
                exit;
            }
        }

        if ((false === $overwrite) && file_exists($repoInterfacePath))
        {
            $this->logger->notice(
                "The repository interface '{$modelClassName}RepositoryInterface' already exists and will not be overwritten."
            );

            return;
        }

        $template = file_get_contents($this->getTemplatesPath() . self::TEMPLATE_PATH_REPOSITORY_INTERFACE);

        $template = str_replace(self::REPLACEMENT_STRING_MODEL_CLASS_NAME, $modelClassName, $template);

        file_put_contents($repoInterfacePath, $template);
        exec("git add $repoInterfacePath");
    }


    protected function getRepositoriesPath()
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository";
    }

    protected function getRepositoryFilePath($modelClassName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository/{$modelClassName}Repository.php";
    }

    protected function getRepositoryInterfacesPath()
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository/Contract";
    }

    protected function getRepositoryInterfacePath($modelClassName)
    {
        $rootPath = $this->getProjectRootPath();

        return $rootPath . "/app/models/Repository/Contract/{$modelClassName}RepositoryInterface.php";
    }
}
