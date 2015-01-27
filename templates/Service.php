<?php
namespace SEOshop\BusinessLogic\<COMPONENT>\Service;

use Psr\Log\LoggerInterface;
use Repository\Contract\<MODELCLASS>RepositoryInterface;
use SEOshop\BusinessLogic\Core\Concept\ServiceAbstract;

/**
 * Class <MODELCLASS>Service
 *
 * @property LoggerInterface               $logger
 * @property <MODELCLASS>RepositoryInterface $repository The repository handled by this service
 *
 * @package Repository
 */
class <MODELCLASS>Service extends ServiceAbstract
{
    /**
     * @param LoggerInterface               $logger
     * @param <MODELCLASS>RepositoryInterface $repository
     */
    public function __construct(
        LoggerInterface $logger,
        <MODELCLASS>RepositoryInterface $repository
    ) {
        $this->logger     = $logger;
        $this->repository = $repository;
    }
}
