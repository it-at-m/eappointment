<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Cluster as Entity;
use \BO\Zmsentities\Collection\ClusterList as Collection;

class Cluster extends Base
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
        $query = new Query\Cluster(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionClusterId($itemId);
        $cluster = $this->fetchOne($query, new Entity());
        if (! $cluster->hasId()) {
            return null;
        }
        $cluster['scopes'] = (new Scope())->readByClusterId($cluster->id, $resolveReferences);
        return $cluster;
    }

     /**
     * read list of clusters
     *
     * @param
     * resolveReferences
     *
     * @return Resource Collection
     */
    public function readList($resolveReferences = 0)
    {
        $clusterList = new Collection();
        $query = new Query\Cluster(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity['scopes'] = (new Scope())->readByClusterId($entity->id, $resolveReferences);
                    $clusterList->addEntity($entity);
                }
            }
        }
        return $clusterList;
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $clusterList = new Collection();
        $query = new Query\Cluster(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity && !$clusterList->hasEntity($entity->id)) {
                    $entity['scopes'] = (new Scope())->readByClusterId($entity->id, $resolveReferences);
                    $clusterList->addEntity($entity);
                }
            }
        }
        return $clusterList;
    }

    /**
     * get a scopeList with opened scopes
     *
     ** @param
     *            clusterId
     *            now
     *
     * @return Bool
     */
    public function readIsOpenedScopeList($clusterId, $now)
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $cluster = $this->readEntity($clusterId, 1);
        if ($cluster && $cluster->toProperty()->scopes->get()) {
            foreach ($cluster->scopes as $scope) {
                $availabilityList = (new Availability())->readOpeningHoursListByDate($scope['id'], $now);
                if ($availabilityList->isOpened($now) && ! $scope->getStatus('ticketprinter', 'deactivated')) {
                    $scopeList->addEntity($scope);
                }
            }
        }
        return $scopeList;
    }

    /**
     * get a scope with shortest waitingtime
     *
     ** @param
     *            clusterId
     *            now
     *
     * @return Bool
     */
    public function readScopeWithShortestWaitingTime($clusterId, $now)
    {
        $scopeList = $this->readIsOpenedScopeList($clusterId, $now);
        $preferedScope = reset($scopeList);
        $waitingTimeReference = 10000000;
        foreach ($scopeList as $scope) {
            $waitingTime = (new Scope())->readWaitingTime($scope->id, $now);
            if ($waitingTime && $waitingTimeReference >= $waitingTime) {
                $waitingTimeReference = $waitingTime;
                $preferedScope = $scope;
            }
        }
        return $preferedScope;
    }

    /**
    * remove an cluster
    *
    * @param
    * itemId
    *
    * @return Resource Status
    */
    public function deleteEntity($itemId)
    {
        $query =  new Query\Cluster(Query\Base::DELETE);
        $query->addConditionClusterId($itemId);
        return $this->deleteItem($query);
    }

    /**
     * write an cluster
     *
     * @param
     * entity
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Cluster $entity)
    {
        $query = new Query\Cluster(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * update an cluster
     *
     * @param
     * clusterId, entity
     *
     * @return Entity
     */
    public function updateEntity($clusterId, \BO\Zmsentities\Cluster $entity)
    {
        $query = new Query\Cluster(Query\Base::UPDATE);
        $query->addConditionClusterId($clusterId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($clusterId);
    }
}
