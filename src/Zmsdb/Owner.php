<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Owner as Entity;
use \BO\Zmsentities\Collection\OwnerList as Collection;

class Owner extends Base
{
    /**
    * read entity
    *
    * @param
    * itemId
    * resolveReferences
    *
    * @return Resource Entity
    */
    public function readEntity($itemId, $resolveReferences = 0)
    {
        $query = new Query\Owner(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOwnerId($itemId);
        $owner = $this->fetchOne($query, new Entity());
        if (1 <= $resolveReferences) {
            $owner['organisations'] = (new Organisation())->readByOwnerId($itemId, $resolveReferences);
        }
        return $owner;
    }

     /**
     * read list of owners
     *
     * @param
     * resolveReferences
     *
     * @return Resource Collection
     */
    public function readList($resolveReferences = 0)
    {
        $ownerList = new Collection();
        $query = new Query\Owner(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $entity = $this->readEntity($entity->id, $resolveReferences);
                if ($entity instanceof Entity) {
                    $ownerList->addEntity($entity);
                }
            }
        }
        return $ownerList;
    }

     /**
     * remove an owner
     *
     * @param
     * itemId
     *
     * @return Resource Status
     */
    public function deleteEntity($itemId)
    {
        $query =  new Query\Owner(Query\Base::DELETE);
        $query->addConditionOwnerId($itemId);
        return $this->deleteItem($query);
    }
}
