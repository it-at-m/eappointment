<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Organisation as Entity;
use \BO\Zmsentities\Collection\OrganisationList as Collection;

class Organisation extends Base
{
    public function readEntity($itemId, $resolveReferences = 0)
    {
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOrganisationId($itemId);
        $organisation = $this->fetchOne($query, new Entity());
        if (isset($organisation['id'])) {
            $organisation['departments'] = (new Department())->readByOrganisationId($itemId, $resolveReferences);
            $organisation['ticketprinters'] = (new Ticketprinter())->readByOrganisationId($itemId, $resolveReferences);
            return $organisation;
        }
        return array();
    }

    public function readByScopeId($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $organisation = $this->fetchOne($query, new Entity());
        if (isset($organisation['id'])) {
            $organisation['departments'] = (new Department())
                ->readByOrganisationId($organisation['id'], $resolveReferences - 1);
        }
        return $organisation;
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $organisation = $this->fetchOne($query, new Entity());
        if (isset($organisation['id']) && 1 < $resolveReferences) {
            $organisation['departments'] = (new Department())
                ->readByOrganisationId($organisation['id'], $resolveReferences - 1);
        }
        return $organisation;
    }

    public function readByClusterId($clusterId, $resolveReferences = 0)
    {
        $scope = (new Scope())->readByClusterId($clusterId, $resolveReferences)->getFirst();
        if (! $scope) {
            throw new Exception\ClusterWithoutScopes();
        }
        return $this->readByScopeId($scope->id, $resolveReferences);
    }

    public function readByOwnerId($ownerId, $resolveReferences = 0)
    {
        $organisationList = new Collection();
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOwnerId($ownerId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $organisation) {
                $entity = $this->readEntity($organisation->id, $resolveReferences - 1);
                if ($entity instanceof Entity) {
                    $organisationList->addEntity($entity);
                }
            }
        }
        return $organisationList;
    }

    public function readList($resolveReferences = 0)
    {
        $organisationList = new Collection();
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $organisation) {
                $entity = new Entity($organisation);
                if ($entity instanceof Entity) {
                    if (1 <= $resolveReferences) {
                        $entity['departments'] = (new Department())->readByOrganisationId(
                            $entity->id,
                            $resolveReferences
                        );
                    }
                    $organisationList->addEntity($entity);
                }
            }
        }
        return $organisationList;
    }

    /**
     * remove an organisation
     *
     * @param
     *            itemId
     *
     * @return Resource Status
     */
    public function deleteEntity($itemId)
    {
        $entity = $this->readEntity($itemId);
        if (0 < $entity->toProperty()->departments->get()->count()) {
            throw new Exception\Organisation\DepartmentListNotEmpty();
        }
        $query = new Query\Organisation(Query\Base::DELETE);
        $query->addConditionOrganisationId($itemId);
        return ($this->deleteItem($query)) ? $entity : null;
    }

    /**
     * write a organisation
     *
     * @param
     *            organisationId
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Organisation $entity, $parentId)
    {
        $query = new Query\Organisation(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $parentId);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()
            ->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * update a organisation
     *
     * @param
     *            organisationId
     *
     * @return Entity
     */
    public function updateEntity($organisationId, \BO\Zmsentities\Organisation $entity, $parentId)
    {
        $query = new Query\Organisation(Query\Base::UPDATE);
        $query->addConditionOrganisationId($organisationId);
        $values = $query->reverseEntityMapping($entity, $parentId);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($organisationId);
    }
}
