<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Cluster as Entity;
use \BO\Zmsentities\Collection\ClusterList as Collection;

/**
 *
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
 *
 */
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
        if ($resolveReferences > 0) {
            $cluster['scopes'] = (new Scope())->readByClusterId($cluster->id, $resolveReferences - 1);
        }
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

    public function readByScopeId($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Cluster(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $entity = $this->fetchOne($query, new Entity());
        if (! $entity->hasId()) {
            return null;
        }
        $entity['scopes'] = (new Scope())->readByClusterId($entity->id, $resolveReferences);
        return $entity;
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
     * get a queueList by cluster id and dateTime
     *
     ** @param
     *            clusterId
     *            now
     *
     * @return Bool
     */
    public function readQueueList(
        $clusterId,
        \DateTimeInterface $dateTime
    ) {
        $cluster = $this->readEntity($clusterId, 1);
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($cluster->scopes as $scope) {
            $scopeQueueList = (new Scope())->readQueueList($scope->id, $dateTime);
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

    public function readEnabledScopeList($clusterId, \DateTimeInterface $dateTime)
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        foreach ($this->readOpenedScopeList($clusterId, $dateTime) as $scope) {
            if ((new Scope())->readIsGivenNumberInContingent($scope['id'])) {
                $scopeList->addEntity($scope);
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
     * update image data for call display image
     *
     * @param
     *         clusterId
     *         Mimepart entity
     *
     * @return Mimepart entity
     */
    public function writeImageData($clusterId, \BO\Zmsentities\Mimepart $entity)
    {
        $imageName = 'c_'. $clusterId .'_bild.'. $entity->mime;
        $statement = $this->getWriter()->prepare((new Query\Scope(Query\Base::REPLACE))->getQueryWriteImageData());
        $statement->execute(array(
            'imagename' => $imageName,
            'imagedata' => $entity->content
        ));
        $entity->id = $clusterId;
        return $entity;
    }

    /**
     * read image data
     *
     * @param
     *         clusterId
     *
     * @return Mimepart entity
     */
    public function readImageData($clusterId)
    {
        $imageName = 'c_'. $clusterId .'_bild';
        $imageData = new \BO\Zmsentities\Mimepart();
        $imageData->content = $this->getReader()->fetchValue(
            (new Query\Scope(Query\Base::SELECT))->getQueryReadImageData(),
            ['imagename' => "%$imageName%"]
        );
        return $imageData;
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
        if ($entity->toProperty()->scopes->isAvailable()) {
            $this->writeAssignedScopes($entity->id, $entity->scopes);
        }
        return $this->readEntity($lastInsertId, 1);
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
        if ($entity->toProperty()->scopes->isAvailable()) {
            $this->writeAssignedScopes($clusterId, $entity->scopes);
        }
        return $this->readEntity($clusterId, 1);
    }

    /**
     * create links preferences of a department
     *
     * @param
     *            departmentId,
     *            links
     *
     * @return Boolean
     */
    protected function writeAssignedScopes($clusterId, $scopeList)
    {
        $deleteStatement = $this->getWriter()->prepare(
            (new Query\Cluster(Query\Base::DELETE))->getQueryDeleteAssignedScopes()
        );
        $deleteStatement->execute(array(
            'clusterId' => $clusterId
        ));
        $writeStatement = $this->getWriter()->prepare(
            (new Query\Cluster(Query\Base::REPLACE))->getQueryWriteAssignedScopes()
        );
        foreach ($scopeList as $scope) {
            $writeStatement->execute(array(
                'clusterId' => $clusterId,
                'scopeId' => $scope['id']
            ));
        }
    }
}
