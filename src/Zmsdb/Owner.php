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

    public function readByOrganisationId($organisationId, $resolveReferences = 0)
    {
        $query = new Query\Owner(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOrganisationId($organisationId);
        return $this->fetchOne($query, new Entity());
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
        $entity = $this->readEntity($itemId, 1);
        if (0 < $entity->toProperty()->organisations->get()->count()) {
            throw new Exception\Owner\OrganisationListNotEmpty();
        }
        $query =  new Query\Owner(Query\Base::DELETE);
        $query->addConditionOwnerId($itemId);
        return ($this->deleteItem($query)) ? $entity : null;
    }

    /**
     * write an owner
     *
     * @param
     * entity
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Owner $entity)
    {
        $query = new Query\Owner(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * update an owner
     *
     * @param
     * ownerId, entity
     *
     * @return Entity
     */
    public function updateEntity($ownerId, \BO\Zmsentities\Owner $entity)
    {
        $query = new Query\Owner(Query\Base::UPDATE);
        $query->addConditionOwnerId($ownerId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($ownerId);
    }
}
