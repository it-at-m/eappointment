<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Cluster as Entity;
use BO\Zmsentities\Collection\ClusterList as Collection;

/**
 *
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Complexity)
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
        return $this->readResolvedReferences($cluster, $resolveReferences);
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences)
    {
        $entity['scopes'] = (new Scope())->readByClusterId($entity->id, $resolveReferences);
        return $entity;
    }

    public function readEntityWithOpenedScopeStatus($itemId, \DateTimeInterface $now, $resolveReferences = 0)
    {
        $entity = $this->readEntity($itemId, $resolveReferences);
        foreach ($entity->scopes as $scope) {
            $scope->setStatusAvailability('isOpened', (new Scope())->readIsOpened($scope->getId(), $now));
        }
        return $entity;
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
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
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
        $entity = $this->readResolvedReferences($entity, $resolveReferences);
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
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
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
        \DateTimeInterface $dateTime,
        $resolveReferences = 0
    ) {
        $cluster = $this->readEntity($clusterId, 1);
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($cluster->scopes as $scope) {
            $scope = (new Scope())->readWithWorkstationCount($scope->id, $dateTime);
            $scopeQueueList = (new Scope())
                ->readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences);
            if (0 < $scopeQueueList->count()) {
                $queueList->addList($scopeQueueList);
            }
        }
        return $queueList->withSortedWaitingTime();
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
                $availabilityList = (new Availability())->readOpeningHoursListByDate($scope['id'], $dateTime, 2);
                if ($availabilityList->isOpened($dateTime)) {
                    $scope->setStatusAvailability('isOpened', true);
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
            if (
                $scope->getCalculatedWorkstationCount() > 0 &&
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
     * get cluster with scopes workstation count
     *
     * * @param
     * scopeId
     * now
     *
     * @return number
     */
    public function readWithScopeWorkstationCount($clusterId, $dateTime, $resolveReferences = 0)
    {
        $scopeQuery = new Scope();
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $cluster = $this->readEntity($clusterId, $resolveReferences);
        if ($cluster->toProperty()->scopes->get()) {
            foreach ($cluster->scopes as $scope) {
                $entity = $scopeQuery->readWithWorkstationCount($scope->id, $dateTime, $resolveReferences = 0);
                if ($entity) {
                    $scopeList->addEntity($entity);
                }
            }
        }
        $cluster->scopes = $scopeList;
        return $cluster;
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
        if ($entity->mime && $entity->content) {
            $this->deleteImage($clusterId);
            $extension = $entity->getExtension();
            if ($extension == 'jpeg') {
                $extension = 'jpg'; //compatibility ZMS1
            }
            $imageName = 'c_' . $clusterId . '_bild.' . $extension;
            $this->perform(
                (new Query\Scope(Query\Base::REPLACE))->getQueryWriteImageData(),
                array(
                'imagename' => $imageName,
                'imagedata' => $entity->content
                )
            );
        }
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
        $imageName = 'c_' . $clusterId . '_bild';
        $imageData = new \BO\Zmsentities\Mimepart();
        $fileData = $this->getReader()->fetchAll(
            (new Query\Scope(Query\Base::SELECT))->getQueryReadImageData(),
            ['imagename' => "$imageName%"]
        );
        if ($fileData) {
            $imageData->content = $fileData[0]['imagecontent'];
            $imageData->mime = pathinfo($fileData[0]['imagename'])['extension'];
        }
        return $imageData;
    }

    /**
     * delete image data for calldisplay image
     *
     * @param
     *         clusterId
     *
     * @return Status
     */
    public function deleteImage($clusterId)
    {
        $imageName = 'c_' . $clusterId . '_bild';
        $result = $this->perform(
            (new Query\Scope(Query\Base::DELETE))->getQueryDeleteImage(),
            array(
            'imagename' => "$imageName%"
            )
        );
        return $result;
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
        $result = false;
        $query =  new Query\Cluster(Query\Base::DELETE);
        $query->addConditionClusterId($itemId);
        if ($this->deleteItem($query)) {
            $result = $this->perform(
                (new Query\Cluster(Query\Base::DELETE))->getQueryDeleteAssignedScopes(),
                ['clusterId' => $itemId]
            );
        }
        return $result;
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
            $this->writeAssignedScopes($lastInsertId, $entity->scopes);
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
        $this->perform(
            (new Query\Cluster(Query\Base::DELETE))->getQueryDeleteAssignedScopes(),
            ['clusterId' => $clusterId]
        );
        foreach ($scopeList as $scope) {
            if (0 < $scope['id']) {
                $this->perform(
                    (new Query\Cluster(Query\Base::REPLACE))->getQueryWriteAssignedScopes(),
                    array(
                    'clusterId' => $clusterId,
                    'scopeId' => $scope['id']
                    )
                );
            }
        }
    }
}
