<?php
namespace Repository\Contract;

use SEOshop\Backend\BigD\Runtime\Contract\RepositoryInterface;

/**
 * Interface <MODELCLASS>RepositoryInterface
 *
 * @method \<MODELCLASS>Query getQuery($withI18n = false) Gets the entity Query
 * @method \<MODELCLASS>Collection | \<MODELCLASS>[] find(array $filters = [], array $includes = [], $withI18n = false, $orderBy = [])
 *         Finds all entries, using a filter and/or includes. Supports nested filters and includes
 * @method \<MODELCLASS> findOneBy(array $filters = [], $includes = [], $withI18n = false) Gets an entity
 * @method \<MODELCLASS> findOneById($id, $includes = [], $withI18n = false) Gets an entity
 * @method \<MODELCLASS> getModel() Gets empty entity
 *
 * @package Repository\Contract
 */
interface <MODELCLASS>RepositoryInterface extends RepositoryInterface
{
}
