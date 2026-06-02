<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Owner as Entity;
use BO\Zmsentities\Collection\OwnerList as Collection;

class Owner extends Base
{
    /**
    * read entity
    *
     * @param int|string $itemId
     * @param int $resolveReferences
     *
     * @return Entity
    */
    public function readEntity($itemId, $resolveReferences = 0)
    {
        $query = new Query\Owner(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOwnerId($itemId);
        $owner = $this->fetchOne($query, new Entity());
        return $this->readResolvedReferences($owner, $resolveReferences);
    }

    #[\Override]
    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences)
    {
        if (0 < $resolveReferences && isset($entity['id'])) {
            $entity['organisations'] = (new Organisation())->readByOwnerId($entity['id'], $resolveReferences - 1);
        }
        return $entity;
    }

     /**
     * read list of owners
     *
     * @param int $resolveReferences
     *
     * @return Collection
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
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                $ownerList->addEntity($entity);
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
        $owner = $this->fetchOne($query, new Entity());
        return $this->readResolvedReferences($owner, $resolveReferences);
    }

    /**
    * remove an owner
    *
     * @param int|string $itemId
     *
     * @return Entity|null
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
     * @param \BO\Zmsentities\Owner $entity
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
     * @param int|string $ownerId
     * @param \BO\Zmsentities\Owner $entity
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
