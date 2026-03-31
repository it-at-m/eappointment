<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Scope as Entity;
use BO\Zmsentities\Collection\ScopeList as Collection;

/**
 *
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(TooManyMethods)
 *
 */
class Scope extends Base
{
    public static $cache = [ ];

    public function readEntity($scopeId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "scope-$scopeId-$resolveReferences";

        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $scope = \App::$cache->get($cacheKey);
        }

        if (empty($scope)) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionScopeId($scopeId);
            $scope = $this->fetchOne($query, new Entity());
            if (! $scope->hasId()) {
                return null;
            }

            if (\App::$cache) {
                \App::$cache->set($cacheKey, $scope);
                if (\App::$log) {
                    \App::$log->info('Scope cache set', ['cache_key' => $cacheKey]);
                }
            }
        }

        return $this->readResolvedReferences($scope, $resolveReferences, $disableCache);
    }

    public function readEntitiesByIds(array $scopeIds, int $resolveReferences = 0, bool $disableCache = false): array
    {
        $scopeIds = array_values(array_unique(array_filter(array_map('intval', $scopeIds))));
        if (!$scopeIds) {
            return [];
        }

        $result  = [];
        $missing = [];
        foreach ($scopeIds as $scopeId) {
            $cacheKey = "{$scopeId}-{$resolveReferences}";
            if (!$disableCache && array_key_exists($cacheKey, self::$cache)) {
                $result[$scopeId] = self::$cache[$cacheKey];
            } else {
                $missing[] = $scopeId;
            }
        }

        if ($missing) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query
                ->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionScopeIds($missing);

            $fetched = $this->fetchList($query, new Entity());
            foreach ($fetched as $entity) {
                if (!$entity->hasId()) {
                    continue;
                }
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                $result[$entity->id] = $entity;
                self::$cache["{$entity->id}-{$resolveReferences}"] = $entity;
            }
        }

        $ordered = [];
        foreach ($scopeIds as $id) {
            if (isset($result[$id])) {
                $ordered[$id] = $result[$id];
            }
        }

        return $ordered;
    }

    public function readResolvedReferences(
        \BO\Zmsentities\Schema\Entity $scope,
        $resolveReferences,
        $disableCache = false
    ) {
        if (0 < $resolveReferences) {
            $scope['dayoff'] = (new DayOff())->readByScopeId($scope->id, $disableCache);
            $scope['closure'] = (new Closure())->readByScopeId($scope->id, $disableCache);
        }
        return $scope;
    }

    public function readByClusterId(
        $clusterId,
        $resolveReferences = 0,
        $disableCache = false
    ) {
        $cacheKey = "scopeReadByClusterId-$clusterId-$resolveReferences";

        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $result = \App::$cache->get($cacheKey);
        }

        if (empty($result)) {
            if ($resolveReferences > 0) {
                $query = new Query\Scope(Query\Base::SELECT);
                $query->addEntityMapping()
                    ->addResolvedReferences($resolveReferences - 1)
                    ->addConditionClusterId($clusterId);
                $result = $this->fetchList($query, new Entity());
            } else {
                $result = $this->getReader()->perform(
                    (new Query\Scope(Query\Base::SELECT))->getQuerySimpleClusterMatch(),
                    [$clusterId]
                );
            }

            if (\App::$cache && !($result instanceof \PDOStatement)) {
                \App::$cache->set($cacheKey, $result);
                if (\App::$log) {
                    \App::$log->info('Scope cache set', ['cache_key' => $cacheKey]);
                }
            }
        }

        $scopeList = new Collection();
        if (!$result) {
            return $scopeList;
        }

        foreach ($result as $entity) {
            if (0 == $resolveReferences) {
                $entity = new Entity(
                    array(
                        'id' => $entity['id'],
                        '$ref' => '/scope/' . $entity['id'] . '/'
                    )
                );
                $scopeList->addEntity($entity);
            } else {
                $scopeList->addEntity($this->readResolvedReferences(
                    $entity,
                    $resolveReferences - 1,
                    $disableCache
                ));
            }
        }

        return $scopeList;
    }

    public function readByProviderId($providerId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "scopeReadByProviderId-$providerId-$resolveReferences";

        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $result = \App::$cache->get($cacheKey);
        }

        if (empty($result)) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionProviderId($providerId);
            $result = $this->fetchList($query, new Entity());

            if (\App::$cache) {
                \App::$cache->set($cacheKey, $result);
                if (\App::$log) {
                    \App::$log->info('Scope cache set', ['cache_key' => $cacheKey]);
                }
            }
        }

        $scopeList = new Collection();
        if (count($result)) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array(
                            'id' => $entity->id,
                            '$ref' => '/scope/' . $entity->id . '/'
                        )
                    );
                    $scopeList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $entity = $this->readResolvedReferences(
                            $entity,
                            $resolveReferences - 1,
                            $disableCache
                        );
                        $scopeList->addEntity($entity);
                    }
                }
            }
        }

        return $scopeList;
    }

    public function readByRequestId($requestId, $source, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $providerList = (new Provider())->readListBySource($source, 0, true, $requestId);

        foreach ($providerList as $provider) {
            $scopeListByProvider = $this->readByProviderId($provider->getId(), $resolveReferences);
            if ($scopeListByProvider->count()) {
                $scopeList->addList($scopeListByProvider);
            }
        }
        return $scopeList->withUniqueScopes();
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "scopeReadByDepartmentId-$departmentId-$resolveReferences";

        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $result = \App::$cache->get($cacheKey);
        }

        $scopeList = new Collection();

        if (empty($result)) {
            if ($resolveReferences > 0) {
                $query = new Query\Scope(Query\Base::SELECT);
                $query->addEntityMapping()
                    ->addResolvedReferences($resolveReferences)
                    ->addConditionDepartmentId($departmentId);
                $result = $this->fetchList($query, new Entity());

                if (\App::$cache) {
                    \App::$cache->set($cacheKey, $result);
                    if (\App::$log) {
                        \App::$log->info('Scope cache set', ['cache_key' => $cacheKey]);
                    }
                }
            } else {
                $result = $this->getReader()->perform(
                    (new Query\Scope(Query\Base::SELECT))->getQuerySimpleDepartmentMatch(),
                    [$departmentId]
                );
            }
        }

        if ($result) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array(
                            'id' => $entity['id'],
                            'contact' => ['name' => $entity['contact__name']],
                            '$ref' => '/scope/' . $entity['id'] . '/'
                        )
                    );
                    $scopeList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $entity = $this->readResolvedReferences($entity, $resolveReferences, $disableCache);
                        $scopeList->addEntity($entity);
                    }
                }
            }
        }

        return $scopeList;
    }
    public function readListBySource($source, $resolveReferences = 0)
    {
        $this->testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addConditionRequestSource($source);
        $query->addEntityMapping();
        $requestList = $this->readCollection($query);
        return ($requestList->count()) ? $requestList->sortByCustomKey('id') : $requestList;
    }

    protected function testSource($source)
    {
        if (! (new Source())->readEntity($source)) {
            throw new Exception\Source\UnknownDataSource();
        }
    }

    protected function readCollection($query)
    {
        $requestList = new Collection();
        $statement = $this->fetchStatement($query);
        while ($requestData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $request = new Entity($query->postProcessJoins($requestData));
            $requestList->addEntity($request);
        }
        return $requestList;
    }

    public function readList($resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "scopeReadList-$resolveReferences";

        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $result = \App::$cache->get($cacheKey);
        }

        if (empty($result)) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences);
            $result = $this->fetchList($query, new Entity());

            if (\App::$cache) {
                \App::$cache->set($cacheKey, $result);
                if (\App::$log) {
                    \App::$log->info('Scope cache set', ['cache_key' => $cacheKey]);
                }
            }
        }

        $scopeList = new Collection();
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $scopeList->addEntity($entity);
                }
            }
        }
        return $scopeList;
    }

    public function readIsOpened($scopeId, $now)
    {
        $isOpened = false;
        $availabilityList = (new Availability())->readOpeningHoursListByDate($scopeId, $now, 2);
        if ($availabilityList->isOpened($now)) {
            $isOpened = true;
        }
        return $isOpened;
    }

    public function readIsEnabled($scopeId, $now)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->setResolveLevel(0)
            ->addConditionScopeId($scopeId);
        $scope = $this->fetchOne($query, new Entity());
        return (
            $this->readIsOpened($scopeId, $now) &&
            $this->readIsGivenNumberInContingent($scopeId) &&
            ! $scope->getStatus('ticketprinter', 'deactivated')
        );
    }

    public function readWaitingNumberUpdated($scopeId, $dateTime, $respectContingent = true)
    {
        if (! $this->readIsGivenNumberInContingent($scopeId) && $respectContingent) {
            throw new Exception\Scope\GivenNumberCountExceeded();
        }
        $this->perform(
            (new Query\Scope(Query\Base::SELECT))->getQueryLastWaitingNumber(),
            ['scope_id' => $scopeId]
        );
        $entity = $this->readEntity($scopeId, 0, true)->updateStatusQueue($dateTime);
        $scope = $this->updateEntity($scopeId, $entity);
        return $scope->getStatus('queue', 'lastGivenNumber');
    }

    public function readDisplayNumberUpdated($scopeId)
    {
        $this->perform(
            (new Query\Scope(Query\Base::SELECT))->getQueryLastDisplayNumber(),
            ['scope_id' => $scopeId]
        );
        $entity = $this->readEntity($scopeId, 0, true)->incrementDisplayNumber();
        $scope = $this->updateEntity($scopeId, $entity);
        return $scope->getStatus('queue', 'lastDisplayNumber');
    }

    public function readIsGivenNumberInContingent($scopeId)
    {
        $isInContingent = $this->getReader()
            ->fetchValue((new Query\Scope(Query\Base::SELECT))
            ->getQueryGivenNumbersInContingent(), ['scope_id' => $scopeId]);
        return ($isInContingent) ? true : false;
    }

    public function readQueueList($scopeIds, $dateTime, $resolveReferences = 0, $withEntities = [])
    {
        if ($resolveReferences > 0) {
            $queueList = (new Process())
                ->readProcessListByScopesAndTime(
                    $scopeIds,
                    $dateTime,
                    $resolveReferences - 1,
                    $withEntities
                )
                ->toQueueList($dateTime);
        } else {
            $queueList = (new Queue())
                ->readListByScopeAndTime($scopeIds, $dateTime, $resolveReferences);
        }

        return $queueList->withSortedArrival();
    }

    public function readWithWorkstationCount($scopeId, $dateTime, $resolveReferences = 0, $withEntities = [])
    {
        $query = new Query\Scope(Query\Base::SELECT, '', false, null, $withEntities);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addResolvedReferences($resolveReferences)
            ->addSelectWorkstationCount($dateTime);
        $scope = $this->fetchOne($query, new Entity());
        $scope = $this->readResolvedReferences($scope, $resolveReferences);
        return ($scope->hasId()) ? $scope : null;
    }

    public function readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences = 0, $withEntities = [])
    {
        $timeAverage = $scope->getPreference('queue', 'processingTimeAverage');
        $scope = (! $timeAverage) ? (new Scope())->readEntity($scope->id) : $scope;
        $queueList = $this->readQueueList([$scope->id], $dateTime, $resolveReferences, $withEntities);
        $workstationCount = $scope->getCalculatedWorkstationCount();
        return $queueList->withEstimatedWaitingTime($timeAverage, $workstationCount, $dateTime);
    }

    public function readScopesQueueListWithWaitingTime(Collection $scopes, $dateTime, $resolveReferences = 0, $withEntities = [])
    {
        $timeSum = 0;
        $workstationCount = 0;
        $scopeIds = [];
        foreach ($scopes as $scope) {
            $timeSum += $scope->getPreference('queue', 'processingTimeAverage');
            $workstationCount += $scope->getCalculatedWorkstationCount();
            $scopeIds[] = $scope->id;
        }

        $timeAverage = $timeSum / $scopes->count();
        $queueList = $this->readQueueList($scopeIds, $dateTime, $resolveReferences, $withEntities);

        return $queueList->withEstimatedWaitingTime($timeAverage, $workstationCount, $dateTime);
    }

    public function readListWithScopeAdminEmail($resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionWithAdminEmail()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $scopeList->addEntity($entity);
                }
            }
        }
        return $scopeList;
    }

    public function writeEntity(\BO\Zmsentities\Scope $entity, $parentId)
    {
        self::$cache = [];
        $query = new Query\Scope(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $parentId);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()
            ->lastInsertId();
        $this->replacePreferences($entity);

        $this->removeCacheByContext($entity, $parentId);

        return $this->readEntity($lastInsertId);
    }

    public function updateEntity($scopeId, \BO\Zmsentities\Scope $entity, $resolveReferences = 0)
    {
        self::$cache = [];

        $departmentId = $this->readDepartmentIdByScopeId($scopeId);

        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($scopeId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->replacePreferences($entity);

        $this->removeCacheByContext($entity, $departmentId);

        return $this->readEntity($scopeId, $resolveReferences, true);
    }

    public function replacePreferences(\BO\Zmsentities\Scope $entity)
    {
        if (isset($entity['preferences'])) {
            $preferenceQuery = new Preferences();
            $entityName = 'scope';
            $entityId = $entity['id'];
            foreach ($entity['preferences'] as $groupName => $groupValues) {
                foreach ($groupValues as $name => $value) {
                    $preferenceQuery->replaceProperty($entityName, $entityId, $groupName, $name, $value);
                }
            }
        }
    }

    public function updateGhostWorkstationCount(\BO\Zmsentities\Scope $entity, \DateTimeInterface $dateTime)
    {
        $departmentId = $this->readDepartmentIdByScopeId($entity->id);

        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($entity->id);
        $values = $query->setGhostWorkstationCountEntityMapping($entity, $dateTime);
        $query->addValues($values);
        $this->writeItem($query);

        $this->removeCacheByContext($entity, $departmentId);

        return $entity;
    }

    public function updateEmergency($scopeId, \BO\Zmsentities\Scope $entity)
    {
        self::$cache = [];
        $departmentId = $this->readDepartmentIdByScopeId($scopeId);

        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($scopeId);
        $values = $query->setEmergencyEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);

        $this->removeCacheByContext($entity, $departmentId);

        return $this->readEntity($scopeId, 0, true);
    }

    public function writeImageData($scopeId, \BO\Zmsentities\Mimepart $entity)
    {
        if ($entity->mime && $entity->content) {
            $this->deleteImage($scopeId);
            $extension = $entity->getExtension();
            if ($extension == 'jpeg') {
                $extension = 'jpg'; //compatibility ZMS1
            }
            $imageName = 's_' . $scopeId . '_bild.' . $extension;
            $this->getWriter()->perform(
                (new Query\Scope(Query\Base::REPLACE))->getQueryWriteImageData(),
                array(
                    'imagename' => $imageName,
                    'imagedata' => $entity->content
                )
            );
        }
        $entity->id = $scopeId;
        return $entity;
    }

    public function readImageData($scopeId)
    {
        $imageName = 's_' . $scopeId . '_bild';
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

    public function deleteImage($scopeId)
    {
        $imageName = 's_' . $scopeId . '_bild';
        return $this->perform((new Query\Scope(Query\Base::DELETE))->getQueryDeleteImage(), array(
            'imagename' => "$imageName%"
        ));
    }

    public function deleteEntity($scopeId)
    {
        $processListCount = (new Process())->readProcessListCountByScope($scopeId);
        if (0 < $processListCount) {
            throw new Exception\Scope\ScopeHasProcesses();
        }
        self::$cache = [];

        $departmentId = $this->readDepartmentIdByScopeId($scopeId);

        $entity = $this->readEntity($scopeId);
        $query = new Query\Scope(Query\Base::DELETE);
        $query->addConditionScopeId($scopeId);
        $this->deletePreferences($entity);

        $this->removeCacheByContext($entity, $departmentId);

        return ($this->deleteItem($query)) ? $entity : null;
    }

    public function deletePreferences(\BO\Zmsentities\Scope $entity)
    {
        $preferenceQuery = new Preferences();
        $entityName = 'scope';
        $entityId = $entity['id'];
        foreach ($entity['preferences'] as $groupName => $groupValues) {
            foreach (array_keys($groupValues) as $name) {
                $preferenceQuery->deleteProperty($entityName, $entityId, $groupName, $name);
            }
        }
    }

    public function readDepartmentIdByScopeId($scopeId)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        return $this->getReader()->fetchValue($query->getQueryDepartmentIdByScopeId(), [$scopeId]);
    }

    public function readClusterIdsByScopeId($scopeId)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $result = $this->getReader()->fetchAll($query->getQueryClusterIdsByScopeId(), [$scopeId]);
        return array_column($result, 'clusterID');
    }

    public function removeCacheByContext($scope, $departmentId = null)
    {
        if (!\App::$cache) {
            return;
        }

        $invalidatedKeys = [];

        // Invalidate scope entity cache for all resolveReferences levels (0, 1, 2)
        if (isset($scope->id)) {
            for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
                $key = "scope-{$scope->id}-{$resolveReferences}";
                if (\App::$cache->has($key)) {
                    \App::$cache->delete($key);
                    $invalidatedKeys[] = $key;
                }
            }
        }

        // Invalidate scope list cache for all resolveReferences levels (0, 1, 2)
        for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
            $key = "scopeReadList-{$resolveReferences}";
            if (\App::$cache->has($key)) {
                \App::$cache->delete($key);
                $invalidatedKeys[] = $key;
            }
        }

        // Invalidate scopeReadByDepartmentId cache for all resolveReferences levels (0, 1, 2)
        if ($departmentId) {
            for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
                $key = "scopeReadByDepartmentId-{$departmentId}-{$resolveReferences}";
                if (\App::$cache->has($key)) {
                    \App::$cache->delete($key);
                    $invalidatedKeys[] = $key;
                }
            }
        }

        // Invalidate scopeReadByClusterId cache for all clusters containing this scope
        if (isset($scope->id)) {
            $clusterIds = $this->readClusterIdsByScopeId($scope->id);
            foreach ($clusterIds as $clusterId) {
                for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
                    $key = "scopeReadByClusterId-{$clusterId}-{$resolveReferences}";
                    if (\App::$cache->has($key)) {
                        \App::$cache->delete($key);
                        $invalidatedKeys[] = $key;
                    }
                }
            }
        }

        // Invalidate scopeReadByProviderId cache for all resolveReferences levels (0, 1, 2)
        if (isset($scope->provider) && isset($scope->provider['id']) && $scope->provider['id']) {
            $providerId = $scope->provider['id'];
            for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
                $key = "scopeReadByProviderId-{$providerId}-{$resolveReferences}";
                if (\App::$cache->has($key)) {
                    \App::$cache->delete($key);
                    $invalidatedKeys[] = $key;
                }
            }
        }

        if (!empty($invalidatedKeys) && \App::$log) {
            \App::$log->info('Scope cache invalidated', [
                'scope_id' => isset($scope->id) ? $scope->id : 'unknown',
                'invalidated_keys' => $invalidatedKeys
            ]);
        }
    }

    public function removeCache($scope)
    {
        $departmentId = isset($scope->id) ? $this->readDepartmentIdByScopeId($scope->id) : null;
        $this->removeCacheByContext($scope, $departmentId);
    }
}
