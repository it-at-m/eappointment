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
     * get a queueList from opened scopes by cluster id and dateTime
     *
     ** @param
     *            clusterId
     *            now
     *
     * @return Bool
     */
    public function readQueueList($clusterId, \DateTimeInterface $dateTime)
    {
        $cluster = $this->readEntity($clusterId, 1);
        $scopeList = $this->readOpenedScopeList($clusterId, $dateTime);
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($scopeList as $scope) {
            $scope = (new Scope())->readWithWorkstationCount($scope['id'], $dateTime);
            $scopeQueueList = (new Scope())->readQueueListWithWaitingTime($scope, $dateTime);
            $scopeQueueList = $scopeQueueList->withShortNameDestinationHint($cluster, $scope);
            if (0 < $scopeQueueList->count()) {
                $queueList->addList($scopeQueueList);
            }
        }
        return $queueList->withSortedArrival();
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
    public function readOpenedScopeList($clusterId, \DateTimeInterface $dateTime)
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $cluster = $this->readEntity($clusterId, 1);
        if ($cluster && $cluster->toProperty()->scopes->get()) {
            foreach ($cluster->scopes as $scope) {
                $availabilityList = (new Availability())->readOpeningHoursListByDate($scope['id'], $dateTime);
                if ($availabilityList->isOpened($dateTime)) {
                    $scopeList->addEntity($scope);
                }
            }
        }
        return $scopeList;
    }

    /**
     * get the scope with shortest estimated waitingtime
     *
     ** @param
     *            clusterId
     *            now
     *
     * @return Bool
     */
    public function readScopeWithShortestWaitingTime($clusterId, \DateTimeInterface $dateTime)
    {
        $scopeList = $this->readOpenedScopeList($clusterId, $dateTime)->getArrayCopy();
        $nextScope = array_shift($scopeList);
        $preferedScope = null;
        $preferedWaitingTime = 0;
        while ($nextScope) {
            $scope = (new Scope())->readWithWorkstationCount($nextScope->id, $dateTime);
            $queueList = (new Scope())->readQueueListWithWaitingTime($scope, $dateTime);
            $data = $scope->getWaitingTimeFromQueueList($queueList, $dateTime);
            if ($scope->getCalculatedWorkstationCount() > 0 &&
                $data &&
                ($data['waitingTimeEstimate'] <= $preferedWaitingTime || 0 == $preferedWaitingTime)
            ) {
                $preferedWaitingTime = $data['waitingTimeEstimate'];
                $preferedScope = $scope;
            }
            $nextScope = array_shift($scopeList);
        }
        if (! $preferedScope) {
            throw new Exception\Cluster\ScopesWithoutWorkstationCount();
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
