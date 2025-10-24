<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Scope as Entity;
use BO\Zmsentities\Collection\ScopeList as Collection;
use BO\Zmsdb\Application as App;

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

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            App::$log->info('ZMSDBCACHE HIT', [
                'cacheKey' => $cacheKey
            ]);
            $scope = App::$cache->get($cacheKey);
        }

        if (empty($scope)) {
            App::$log->info('ZMSDBCACHE NOT HIT', [
                'cacheKey' => $cacheKey,
                'disableCache' => $disableCache
            ]);
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionScopeId($scopeId);
            $scope = $this->fetchOne($query, new Entity());
            if (! $scope->hasId()) {
                return null;
            }

            if (App::$cache) {
                $res = App::$cache->set($cacheKey, $scope);
                App::$log->info('ZMSDBCACHE SAVED', [
                    'cacheKey' => $cacheKey,
                    'res' => $res
                ]);
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

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            App::$log->info('ZMSDBCACHE HIT', [
                'cacheKey' => $cacheKey
            ]);
            $result = App::$cache->get($cacheKey);
        }

        if (empty($result)) {
            App::$log->info('ZMSDBCACHE NOT HIT', [
                'cacheKey' => $cacheKey,
                'disableCache' => $disableCache
            ]);
            if ($resolveReferences > 0) {
                $query = new Query\Scope(Query\Base::SELECT);
                $query->addEntityMapping()
                    ->addResolvedReferences($resolveReferences - 1)
                    ->addConditionClusterId($clusterId);
                $result = $this->fetchList($query, new Entity());
            } else {
                $queryResult = $this->getReader()->perform(
                    (new Query\Scope(Query\Base::SELECT))->getQuerySimpleClusterMatch(),
                    [$clusterId]
                );

                $result = [];
                foreach ($queryResult as $entity) {
                    $result[] = new Entity(
                        array(
                            'id' => $entity['id'],
                            '$ref' => '/scope/' . $entity['id'] . '/'
                        )
                    );
                }
            }

            if (App::$cache) {
                $res = App::$cache->set($cacheKey, $result);
                App::$log->info('ZMSDBCACHE SAVED', [
                    'cacheKey' => $cacheKey,
                    'res' => $res
                ]);
            }
        }

        $scopeList = new Collection();
        if (!$result) {
            return $scopeList;
        }

        foreach ($result as $entity) {
            if (0 == $resolveReferences) {
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

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            App::$log->info('ZMSDBCACHE HIT', [
                'cacheKey' => $cacheKey
            ]);
            $result = App::$cache->get($cacheKey);
        }

        if (empty($result)) {
            App::$log->info('ZMSDBCACHE NOT HIT', [
                'cacheKey' => $cacheKey,
                'disableCache' => $disableCache
            ]);
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionProviderId($providerId);
            $result = $this->fetchList($query, new Entity());

            if (App::$cache) {
                $res = App::$cache->set($cacheKey, $result);
                App::$log->info('ZMSDBCACHE SAVED', [
                    'cacheKey' => $cacheKey,
                    'res' => $res
                ]);
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

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            App::$log->info('ZMSDBCACHE HIT', [
                'cacheKey' => $cacheKey
            ]);
            $result = App::$cache->get($cacheKey);
        }

        $scopeList = new Collection();

        if (empty($result)) {
            App::$log->info('ZMSDBCACHE NOT HIT', [
                'cacheKey' => $cacheKey,
                'disableCache' => $disableCache
            ]);
            if ($resolveReferences > 0) {
                $query = new Query\Scope(Query\Base::SELECT);
                $query->addEntityMapping()
                    ->addResolvedReferences($resolveReferences)
                    ->addConditionDepartmentId($departmentId);
                $result = $this->fetchList($query, new Entity());
            } else {
                $queryResult = $this->getReader()->perform(
                    (new Query\Scope(Query\Base::SELECT))->getQuerySimpleDepartmentMatch(),
                    [$departmentId]
                );

                $result = [];
                foreach ($queryResult as $entity) {
                    $result[] = new Entity(
                        array(
                            'id' => $entity['id'],
                            'contact' => ['name' => $entity['contact__name']],
                            '$ref' => '/scope/' . $entity['id'] . '/'
                        )
                    );
                }
            }

            if (App::$cache) {
                $res = App::$cache->set($cacheKey, $result);
                App::$log->info('ZMSDBCACHE SAVED', [
                    'cacheKey' => $cacheKey,
                    'res' => $res,
                    'time' => microtime(true)
                ]);
            }
        }

        if ($result) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
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

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            App::$log->info('ZMSDBCACHE HIT', [
                'cacheKey' => $cacheKey
            ]);
            $result = App::$cache->get($cacheKey);
        }

        if (empty($result)) {
            App::$log->info('ZMSDBCACHE NOT HIT', [
                'cacheKey' => $cacheKey,
                'disableCache' => $disableCache
            ]);
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences);
            $result = $this->fetchList($query, new Entity());

            if (App::$cache) {
                $res = App::$cache->set($cacheKey, $result);
                App::$log->info('ZMSDBCACHE SAVED', [
                    'cacheKey' => $cacheKey,
                    'res' => $res
                ]);
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

    /**
     * get a scope and return true if it is opened
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
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

    /**
     * get last given waitingnumer and return updated (+1) waitingnumber
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
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

    /**
     * get last given waitingnumer and return updated (+1) waitingnumber
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
    public function readIsGivenNumberInContingent($scopeId)
    {
        $isInContingent = $this->getReader()
            ->fetchValue((new Query\Scope(Query\Base::SELECT))
            ->getQueryGivenNumbersInContingent(), ['scope_id' => $scopeId]);
        return ($isInContingent) ? true : false;
    }

    /**
     * get list of queues on scope by daytime
     *
     * * @param
     * scopeId
     * now
     *
     * @return number
     */
    public function readQueueList($scopeId, $dateTime, $resolveReferences = 0)
    {
        if ($resolveReferences > 0) {
            // resolveReferences > 0 is only necessary for a resolved process
            $queueList = (new Process())
                ->readProcessListByScopeAndTime($scopeId, $dateTime, $resolveReferences - 1)
                ->toQueueList($dateTime);
        } else {
            $queueList = (new Queue())
                ->readListByScopeAndTime($scopeId, $dateTime, $resolveReferences);
        }
        return $queueList->withSortedArrival();
    }

    /**
     * get waitingtime of scope
     *
     * * @param
     * scopeId
     * now
     *
     * @return \BO\Zmsentities\Scope
     */
    public function readWithWorkstationCount($scopeId, $dateTime, $resolveReferences = 0)
    {
        //get scope
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addResolvedReferences($resolveReferences)
            ->addSelectWorkstationCount($dateTime);
        $scope = $this->fetchOne($query, new Entity());
        $scope = $this->readResolvedReferences($scope, $resolveReferences);
        return ($scope->hasId()) ? $scope : null;
    }

    public function readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences = 0)
    {
        $timeAverage = $scope->getPreference('queue', 'processingTimeAverage');
        $scope = (! $timeAverage) ? (new Scope())->readEntity($scope->id) : $scope;
        $queueList = $this->readQueueList($scope->id, $dateTime, $resolveReferences);
        $timeAverage = $scope->getPreference('queue', 'processingTimeAverage');
        $workstationCount = $scope->getCalculatedWorkstationCount();
        return $queueList->withEstimatedWaitingTime($timeAverage, $workstationCount, $dateTime);
    }

    /**
     * get list of scopes with admin
     *
     * * @param
     * scopeId
     * now
     *
     * @return number
     */
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

    /**
     * write a scope
     *
     * @return Entity
     */
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

        $this->removeCache($entity);

        return $this->readEntity($lastInsertId);
    }

    /**
     * update a scope
     *
     * @param
     *            scopeId
     *
     * @return Entity
     */
    public function updateEntity($scopeId, \BO\Zmsentities\Scope $entity, $resolveReferences = 0)
    {
        self::$cache = [];
        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($scopeId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->replacePreferences($entity);

        $this->removeCache($entity);

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

            $this->removeCache($entity);
        }
    }

    /**
     * update ghostWorkstationCount
     *
     * @param
     *         scopeId
     *         entity
     *         dateTime (now)
     *
     * @return Entity
     */
    public function updateGhostWorkstationCount(\BO\Zmsentities\Scope $entity, \DateTimeInterface $dateTime)
    {
        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($entity->id);
        $values = $query->setGhostWorkstationCountEntityMapping($entity, $dateTime);
        $query->addValues($values);
        $this->writeItem($query);

        $this->removeCache($entity);

        return $entity;
    }

    /**
     * update emergency
     *
     * @param
     *         scopeId
     *         entity
     *
     * @return Entity
     */
    public function updateEmergency($scopeId, \BO\Zmsentities\Scope $entity)
    {
        self::$cache = [];
        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($scopeId);
        $values = $query->setEmergencyEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);

        $this->removeCache($entity);

        return $this->readEntity($scopeId, 0, true);
    }

    /**
     * update image data for call display image
     *
     * @param
     *         scopeId
     *         Mimepart entity
     *
     * @return Mimepart entity
     */
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

    /**
     * read image data
     *
     * @param
     *         scopeId
     *
     * @return Mimepart entity
     */
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

    /**
     * delete image data for call display image
     *
     * @param
     *         scopeId
     *
     * @return Status
     */
    public function deleteImage($scopeId)
    {
        $imageName = 's_' . $scopeId . '_bild';
        return $this->perform((new Query\Scope(Query\Base::DELETE))->getQueryDeleteImage(), array(
            'imagename' => "$imageName%"
        ));
    }

    /**
     * remove a scope
     *
     * @param
     *            scopeId
     *
     * @return Resource Status
     */
    public function deleteEntity($scopeId)
    {
        $processListCount = (new Process())->readProcessListCountByScope($scopeId);
        if (0 < $processListCount) {
            throw new Exception\Scope\ScopeHasProcesses();
        }
        self::$cache = [];
        $entity = $this->readEntity($scopeId);
        $query = new Query\Scope(Query\Base::DELETE);
        $query->addConditionScopeId($scopeId);
        $this->deletePreferences($entity);

        $this->removeCache($entity);

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

        $this->removeCache($entity);
    }

    public function removeCache($scope)
    {
        if (!App::$cache) {
            return;
        }

        if (isset($scope->provider) && isset($this->provider->id)) {
            if (App::$cache->has('scopeReadByProviderId-' . $scope->getProviderId() . '-0')) {
                App::$cache->delete('scopeReadByProviderId-' . $scope->getProviderId() . '-0');
            }

            if (App::$cache->has('scopeReadByProviderId-' . $scope->getProviderId() . '-1')) {
                App::$cache->delete('scopeReadByProviderId-' . $scope->getProviderId() . '-1');
            }

            if (App::$cache->has('scopeReadByProviderId-' . $scope->getProviderId() . '-2')) {
                App::$cache->delete('scopeReadByProviderId-' . $scope->getProviderId() . '-2');
            }
        }

        if (isset($scope->id)) {
            if (App::$cache->has("scope-$scope->id-0")) {
                App::$cache->delete("scope-$scope->id-0");
            }

            if (App::$cache->has("scope-$scope->id-1")) {
                App::$cache->delete("scope-$scope->id-1");
            }

            if (App::$cache->has("scope-$scope->id-2")) {
                App::$cache->delete("scope-$scope->id-2");
            }
        }
    }
}
