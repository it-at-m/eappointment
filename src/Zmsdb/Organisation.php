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
            return $this->readResolvedReferences($organisation, $resolveReferences);
        }
        return array();
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            $entity['departments'] = (new Department())
                ->readByOrganisationId($entity->id, $resolveReferences - 1);
            $entity['ticketprinters'] = (new Ticketprinter())
                ->readByOrganisationId($entity->id, $resolveReferences - 1);
        }
        return $entity;
    }

    public function readByScopeId($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $organisation = $this->fetchOne($query, new Entity());
        return $this->readResolvedReferences($organisation, $resolveReferences);
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $query = new Query\Organisation(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $organisation = $this->fetchOne($query, new Entity());
        return $this->readResolvedReferences($organisation, $resolveReferences);
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
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
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
        $entity = $this->readEntity($itemId, 1);
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
        if ($entity->toProperty()->ticketprinters->isAvailable()) {
            $this->writeOrganisationTicketprinters($lastInsertId, $entity->ticketprinters);
        }
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
    public function updateEntity($organisationId, \BO\Zmsentities\Organisation $entity)
    {
        $query = new Query\Organisation(Query\Base::UPDATE);
        $query->addConditionOrganisationId($organisationId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        if ($entity->toProperty()->ticketprinters->isAvailable()) {
            $this->writeOrganisationTicketprinters($organisationId, $entity->ticketprinters);
        }
        return $this->readEntity($organisationId, 1);
    }

    /**
     * create ticketprinters of an organisation
     *
     * @param
     *            organisationID,
     *            ticketprinterList
     *
     * @return Boolean
     */
    protected function writeOrganisationTicketprinters($organisationId, $ticketprinterList)
    {
        $deleteQuery = new Query\Ticketprinter(Query\Base::DELETE);
        $deleteQuery->addConditionOrganisationId($organisationId);
        $this->deleteItem($deleteQuery);
        foreach ($ticketprinterList as $ticketprinter) {
            $ticketprinter['enabled'] = (isset($ticketprinter['enabled'])) ? 1 : 0;
            $ticketprinter = new \BO\Zmsentities\Ticketprinter($ticketprinter);
            $query = new Ticketprinter();
            $query->writeEntity($ticketprinter, $organisationId);
        }
    }
}
