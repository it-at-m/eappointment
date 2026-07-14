<?php

namespace BO\Zmsbackend\Cluster\Service;

use BO\Zmsentities\Cluster as Entity;
use BO\Zmsentities\Collection\ClusterList as Collection;

/**
 *
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Complexity)
 *
 */
class Cluster extends \BO\Zmsbackend\Base
{
    public function readEntity($itemId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "cluster-$itemId-$resolveReferences";

        $cluster = null;
        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $cluster = \App::$cache->get($cacheKey);
        }

        if (empty($cluster)) {
            $query = new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::SELECT);
            $query
                ->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionClusterId($itemId);
            $cluster = $this->fetchOne($query, new Entity());
            if (! $cluster->hasId()) {
                return null;
            }

            if (\App::$cache) {
                \App::$cache->set($cacheKey, $cluster);
                if (\App::$log) {
                    \App::$log->info('Cluster cache set', ['cache_key' => $cacheKey]);
                }
            }
        }

        return $this->readResolvedReferences($cluster, $resolveReferences, $disableCache);
    }

    #[\Override]
    public function readResolvedReferences(
        \BO\Zmsentities\Schema\Entity $entity,
        $resolveReferences,
        $disableCache = false
    ) {
        $entity['scopes'] = (new \BO\Zmsbackend\Scope\Service\Scope())->readByClusterId($entity->id, $resolveReferences, $disableCache);

        return $entity;
    }

    public function readEntityWithOpenedScopeStatus($itemId, \DateTimeInterface $now, $resolveReferences = 0)
    {
        $entity = $this->readEntity($itemId, $resolveReferences);
        foreach ($entity->scopes as $scope) {
            $scope->setStatusAvailability('isOpened', (new \BO\Zmsbackend\Scope\Service\Scope())->readIsOpened($scope->getId(), $now));
        }
        return $entity;
    }

    public function readList($resolveReferences = 0)
    {
        $clusterList = new Collection();
        $query = new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::SELECT);
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
        $query = new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::SELECT);
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

    public function readByDepartmentId($departmentId, $resolveReferences = 0, $disableCache = false)
    {
        $clusterList = new Collection();
        $query = new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity && !$clusterList->hasEntity($entity->id)) {
                    $entity = $this->readResolvedReferences($entity, $resolveReferences, $disableCache);
                    $clusterList->addEntity($entity);
                }
            }
        }
        return $clusterList;
    }

    public function readQueueList(
        $clusterId,
        \DateTimeInterface $dateTime,
        $resolveReferences = 0,
        $withEntities = []
    ) {
        $cluster = $this->readEntity($clusterId, 1);

        return (new \BO\Zmsbackend\Scope\Service\Scope())
            ->readScopesQueueListWithWaitingTime($cluster->scopes, $dateTime, $resolveReferences, $withEntities)
            ->withSortedWaitingTime();
    }

    public function readOpenedScopeList($clusterId, \DateTimeInterface $dateTime)
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $cluster = $this->readEntity($clusterId, 1);
        if ($cluster && $cluster->toProperty()->scopes->get()) {
            foreach ($cluster->scopes as $scope) {
                $availabilityList = (new \BO\Zmsbackend\Availability\Service\Availability())->readOpeningHoursListByDate($scope['id'], $dateTime, 2);
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
            if ((new \BO\Zmsbackend\Scope\Service\Scope())->readIsGivenNumberInContingent($scope['id'])) {
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList;
    }

    public function readScopeWithShortestWaitingTime($clusterId, \DateTimeInterface $dateTime)
    {
        $scopeList = $this->readOpenedScopeList($clusterId, $dateTime)->getArrayCopy();
        $nextScope = array_shift($scopeList);
        $preferedScope = null;
        $preferedWaitingTime = 0;
        while ($nextScope) {
            $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readWithWorkstationCount($nextScope->id, $dateTime);
            $queueList = (new \BO\Zmsbackend\Scope\Service\Scope())->readQueueListWithWaitingTime($scope, $dateTime);
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
            throw new \BO\Zmsbackend\Cluster\Exception\ScopesWithoutWorkstationCount();
        }
        return $preferedScope;
    }

    public function readWithScopeWorkstationCount($clusterId, $dateTime, $resolveReferences = 0)
    {
        $scopeQuery = new \BO\Zmsbackend\Scope\Service\Scope();
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
                (new \BO\Zmsbackend\Query\Scope(\BO\Zmsbackend\Query\Base::REPLACE))->getQueryWriteImageData(),
                array(
                'imagename' => $imageName,
                'imagedata' => $entity->content
                )
            );
        }
        $entity->id = $clusterId;
        return $entity;
    }

    public function readImageData($clusterId)
    {
        $imageName = 'c_' . $clusterId . '_bild';
        $imageData = new \BO\Zmsentities\Mimepart();
        $fileData = $this->getReader()->fetchAll(
            (new \BO\Zmsbackend\Query\Scope(\BO\Zmsbackend\Query\Base::SELECT))->getQueryReadImageData(),
            ['imagename' => "$imageName%"]
        );
        if ($fileData) {
            $imageData->content = $fileData[0]['imagecontent'];
            $imageData->mime = pathinfo($fileData[0]['imagename'])['extension'];
        }
        return $imageData;
    }

    public function deleteImage($clusterId)
    {
        $imageName = 'c_' . $clusterId . '_bild';
        $result = $this->perform(
            (new \BO\Zmsbackend\Query\Scope(\BO\Zmsbackend\Query\Base::DELETE))->getQueryDeleteImage(),
            array(
            'imagename' => "$imageName%"
            )
        );
        return $result;
    }

    public function deleteEntity($itemId)
    {
        $result = false;
        $cluster = $this->readEntity($itemId);
        $query =  new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::DELETE);
        $query->addConditionClusterId($itemId);
        if ($this->deleteItem($query)) {
            $result = $this->perform(
                (new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::DELETE))->getQueryDeleteAssignedScopes(),
                ['clusterId' => $itemId]
            );
        }

        $this->removeCache($cluster);

        return $result;
    }

    public function writeEntity(\BO\Zmsentities\Cluster $entity)
    {
        $query = new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        if ($entity->toProperty()->scopes->isAvailable()) {
            $this->writeAssignedScopes($lastInsertId, $entity->scopes);
        }

        $this->removeCache($entity);

        return $this->readEntity($lastInsertId, 1, true);
    }

    public function updateEntity($clusterId, \BO\Zmsentities\Cluster $entity)
    {
        $query = new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::UPDATE);
        $query->addConditionClusterId($clusterId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        if ($entity->toProperty()->scopes->isAvailable()) {
            $this->writeAssignedScopes($clusterId, $entity->scopes);
        }

        $this->removeCache($entity);

        return $this->readEntity($clusterId, 1, true);
    }

    protected function writeAssignedScopes($clusterId, $scopeList)
    {
        $cluster = $this->readEntity($clusterId);
        $this->perform(
            (new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::DELETE))->getQueryDeleteAssignedScopes(),
            ['clusterId' => $clusterId]
        );
        foreach ($scopeList as $scope) {
            if (0 < $scope['id']) {
                $this->perform(
                    (new \BO\Zmsbackend\Cluster\Repository\Cluster(\BO\Zmsbackend\Query\Base::REPLACE))->getQueryWriteAssignedScopes(),
                    array(
                    'clusterId' => $clusterId,
                    'scopeId' => $scope['id']
                    )
                );
            }
        }

        $this->removeCache($cluster);
    }

    public function removeCache($cluster)
    {
        if (!\App::$cache || !isset($cluster->id)) {
            return;
        }

        $invalidatedKeys = [];

        // Invalidate cluster entity cache for all resolveReferences levels (0, 1, 2)
        for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
            $key = "cluster-{$cluster->id}-{$resolveReferences}";
            if (\App::$cache->has($key)) {
                \App::$cache->delete($key);
                $invalidatedKeys[] = $key;
            }
        }

        // Invalidate scopeReadByClusterId cache for all resolveReferences levels (0, 1, 2)
        for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
            $key = "scopeReadByClusterId-{$cluster->id}-{$resolveReferences}";
            if (\App::$cache->has($key)) {
                \App::$cache->delete($key);
                $invalidatedKeys[] = $key;
            }
        }

        if (!empty($invalidatedKeys) && \App::$log) {
            \App::$log->info('Cluster cache invalidated', [
                'cluster_id' => $cluster->id,
                'invalidated_keys' => $invalidatedKeys
            ]);
        }
    }
}
