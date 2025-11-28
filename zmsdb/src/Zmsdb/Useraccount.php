<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Useraccount as Entity;
use BO\Zmsentities\Collection\UseraccountList as Collection;

/**
 * @SuppressWarnings(Public)
 * @SuppressWarnings(TooManyMethods)
 * @SuppressWarnings(ExcessiveClassComplexity)
 *
 */
class Useraccount extends Base
{
    private const CACHE_VERSION_KEY = 'useraccountCacheVersion';
    private const CACHE_INDEX_PREFIX = 'useraccountCacheIndex-';
    private const CACHE_INDEX_GLOBAL = 'all';

    /**
     * Read or initialize the cache version for all useraccount-related cache entries.
     */
    protected function getUseraccountCacheVersion(): int
    {
        if (!App::$cache) {
            return 1;
        }

        $version = App::$cache->get(self::CACHE_VERSION_KEY);
        if (!is_int($version) || $version < 1) {
            $version = 1;
            App::$cache->set(self::CACHE_VERSION_KEY, $version);
        }

        return $version;
    }

    /**
     * Register a cache key for one or more department IDs so we can invalidate
     * only the affected department-based caches later on.
     */
    protected function registerCacheKeyForDepartments(array $departmentIds, string $cacheKey): void
    {
        if (!App::$cache || empty($departmentIds)) {
            return;
        }

        $departmentIds = array_values(array_unique(array_filter($departmentIds, function ($id) {
            return $id !== null && $id !== '';
        })));

        foreach ($departmentIds as $departmentId) {
            $indexKey = $this->getCacheIndexKey($departmentId);
            $existing = App::$cache->get($indexKey);
            if (!is_array($existing)) {
                $existing = [];
            }
            if (!in_array($cacheKey, $existing, true)) {
                $existing[] = $cacheKey;
                App::$cache->set($indexKey, $existing);
            }
        }
    }

    protected function deleteCacheKey(string $cacheKey): bool
    {
        if (!App::$cache) {
            return false;
        }

        if (App::$cache->has($cacheKey)) {
            App::$cache->delete($cacheKey);
            return true;
        }

        return false;
    }

    protected function invalidateDepartmentCaches(array $departmentIds): bool
    {
        if (!App::$cache) {
            return false;
        }

        $departmentIds = array_values(array_unique(array_filter($departmentIds, function ($id) {
            return $id !== null && $id !== '';
        })));

        $foundAny = false;

        foreach ($departmentIds as $departmentId) {
            $indexKey = $this->getCacheIndexKey($departmentId);
            $indexExists = App::$cache->has($indexKey);
            $cacheKeys = $indexExists ? App::$cache->get($indexKey) : [];
            if (!is_array($cacheKeys)) {
                $cacheKeys = [];
            }

            if ($indexExists) {
                $foundAny = true;
            }

            foreach ($cacheKeys as $cacheKey) {
                if ($this->deleteCacheKey($cacheKey)) {
                    $foundAny = true;
                }
            }

            App::$cache->delete($indexKey);
        }

        return $foundAny;
    }

    protected function getCacheIndexKey($departmentId): string
    {
        return self::CACHE_INDEX_PREFIX . $departmentId;
    }

    protected function extractDepartmentIdsFromEntity($useraccount): array
    {
        if (!isset($useraccount->departments) || empty($useraccount->departments)) {
            return [];
        }

        $ids = [];
        foreach ($useraccount->departments as $department) {
            if (is_object($department) && isset($department->id)) {
                $ids[] = $department->id;
            } elseif (is_array($department) && isset($department['id'])) {
                $ids[] = $department['id'];
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    protected function readDepartmentIdsForLoginName($loginName): array
    {
        if (!$loginName) {
            return [];
        }

        $query = Query\Useraccount::QUERY_READ_ASSIGNED_DEPARTMENTS;
        $departmentData = $this->getReader()->fetchAll($query, ['useraccountName' => $loginName]);

        $ids = [];
        foreach ($departmentData as $row) {
            if (isset($row['id'])) {
                $ids[] = $row['id'];
            }
        }

        return array_values(array_unique($ids));
    }

    protected function collectDepartmentIdsForInvalidation($useraccount, array $previousDepartmentIds = []): array
    {
        $currentFromEntity = $this->extractDepartmentIdsFromEntity($useraccount);
        $currentFromDatabase = $this->readDepartmentIdsForLoginName($useraccount->id ?? null);

        $departmentIds = array_merge(
            $previousDepartmentIds,
            $currentFromEntity,
            $currentFromDatabase
        );

        $departmentIds[] = self::CACHE_INDEX_GLOBAL;

        return array_values(array_unique(array_filter($departmentIds, function ($id) {
            return $id !== null && $id !== '';
        })));
    }

    protected function collectUseraccountIdentifiers($useraccount): array
    {
        $identifiers = [];

        if (isset($useraccount->id)) {
            $identifiers[] = $useraccount->id;
        }

        if (isset($useraccount->loginname) && $useraccount->loginname !== $useraccount->id) {
            $identifiers[] = $useraccount->loginname;
        }

        return array_values(array_unique(array_filter($identifiers, function ($identifier) {
            return $identifier !== null && $identifier !== '';
        })));
    }
    /**
     * Sanitize cache key by replacing reserved characters
     * Reserved characters: {}()/\@:
     */
    protected function sanitizeCacheKey($key)
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '_', $key);
    }

    public function readIsUserExisting($loginName, $password = false)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->setResolveLevel(0)
            ->addConditionLoginName($loginName);
        if ($password) {
            $query->addConditionPassword($password);
        }
        $useraccount = $this->fetchOne($query, new Entity());
        return ($useraccount->hasId()) ? true : false;
    }

    public function readEntity($loginname, $resolveReferences = 1, $disableCache = false)
    {
        $version = $this->getUseraccountCacheVersion();
        $cacheKey = $this->sanitizeCacheKey("useraccount-v{$version}-$loginname-$resolveReferences");
        $useraccount = null;

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            $useraccount = App::$cache->get($cacheKey);
            if ($useraccount && App::$log) {
                App::$log->info('Useraccount cache hit', [
                    'cache_key' => $cacheKey,
                    'loginname' => $loginname,
                    'resolveReferences' => $resolveReferences,
                ]);
            }
        }

        if (empty($useraccount)) {
            $query = new Query\Useraccount(Query\Base::SELECT);
            $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionLoginName($loginname);
            $useraccount = $this->fetchOne($query, new Entity());
            if (!$useraccount->hasId()) {
                return null;
            }

            $useraccount = $this->readResolvedReferences($useraccount, $resolveReferences);

            if (App::$cache) {
                App::$cache->set($cacheKey, $useraccount);
                if (App::$log) {
                    App::$log->info('Useraccount cache set', [
                        'cache_key' => $cacheKey,
                        'loginname' => $loginname,
                        'resolveReferences' => $resolveReferences,
                        'useraccount_id' => $useraccount->id ?? null
                    ]);
                }
            }
        }

        return $useraccount;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $useraccount, $resolveReferences)
    {
        if (0 < $resolveReferences && $useraccount->toProperty()->id->get()) {
            $useraccount->departments = $this->readAssignedDepartmentList($useraccount, $resolveReferences);
        }
        return $useraccount;
    }

    /**
     * @SuppressWarnings(NPathComplexity)
     */
    public function readList($resolveReferences = 0, $disableCache = false, $workstation = null)
    {
        $version = $this->getUseraccountCacheVersion();
        $workstationKey = '';
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $workstationKey = '-workstation-' . $workstation->getUseraccount()->id;
        }
        $cacheKey = "useraccountReadList-v{$version}-$resolveReferences$workstationKey";
        $result = null;

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            $result = App::$cache->get($cacheKey);
            if ($result && App::$log) {
                App::$log->info('Useraccount list cache hit', [
                    'cache_key' => $cacheKey,
                    'resolveReferences' => $resolveReferences,
                    'count' => $result->count()
                ]);
            }
        }

        if (empty($result)) {
            $collection = new Collection();
            $query = new Query\Useraccount(Query\Base::SELECT);
            $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

            // Apply workstation access filtering if provided
            if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
                $workstationUserId = $this->readEntityIdByLoginName($workstation->getUseraccount()->id);
                $workstationDepartmentIds = $workstation->getDepartmentList()->getIds();

                // If no departments loaded, return empty result for security
                if (empty($workstationDepartmentIds)) {
                    $result = new Collection();
                    return $result;
                }

                $query->addConditionWorkstationAccess(
                    $workstationUserId,
                    $workstationDepartmentIds,
                    $workstation->getUseraccount()->isSuperUser()
                );
            }

            $result = $this->fetchList($query, new Entity());
            if (count($result)) {
                foreach ($result as $entity) {
                    $collection->addEntity($entity);
                }
                if (0 < $resolveReferences) {
                    $departmentMap = $this->readAssignedDepartmentListsForAll($collection, $resolveReferences - 1);
                    foreach ($collection as $entity) {
                        if (isset($departmentMap[$entity->id])) {
                            $entity->departments = $departmentMap[$entity->id];
                        }
                    }
                }
            }
            $result = $collection;

            if (App::$cache) {
                App::$cache->set($cacheKey, $result);
                $this->registerCacheKeyForDepartments([self::CACHE_INDEX_GLOBAL], $cacheKey);
                if (App::$log) {
                    App::$log->info('Useraccount list cache set', [
                        'cache_key' => $cacheKey,
                        'resolveReferences' => $resolveReferences,
                        'count' => $result->count()
                    ]);
                }
            }
        }

        return $result;
    }

    protected function readListStatement($statement, $resolveReferences)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $collection = new Collection();
        while ($userAccountData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcessJoins($userAccountData));
            $collection->addEntity($entity);
        }
        if (0 < $resolveReferences && count($collection) > 0) {
            $departmentMap = $this->readAssignedDepartmentListsForAll($collection, $resolveReferences - 1);
            foreach ($collection as $entity) {
                if (isset($departmentMap[$entity->id])) {
                    $entity->departments = $departmentMap[$entity->id];
                }
            }
        }
        return $collection;
    }

    public function readAssignedDepartmentList($useraccount, $resolveReferences = 0)
    {
        if ($useraccount->isSuperUser()) {
            $query = Query\Useraccount::QUERY_READ_SUPERUSER_DEPARTMENTS;
            $departmentData = $this->getReader()->fetchAll($query);
        } else {
            $query = Query\Useraccount::QUERY_READ_ASSIGNED_DEPARTMENTS;
            $departmentData = $this->getReader()->fetchAll($query, ['useraccountName' => $useraccount->id]);
        }
        return $this->buildDepartmentList($departmentData, $resolveReferences);
    }

    protected function readAssignedDepartmentListsForAll(Collection $useraccounts, $resolveReferences = 0)
    {
        if (count($useraccounts) === 0) {
            return [];
        }

        list($superusers, $regularUsers) = $this->separateSuperusersFromRegularUsers($useraccounts);
        $result = [];

        if (count($superusers) > 0) {
            $result = array_merge($result, $this->loadSuperuserDepartments($superusers, $resolveReferences));
        }

        if (count($regularUsers) > 0) {
            $result = array_merge($result, $this->loadRegularUserDepartments($regularUsers, $resolveReferences));
        }

        return $result;
    }

    protected function separateSuperusersFromRegularUsers(Collection $useraccounts)
    {
        $superusers = [];
        $regularUsers = [];
        foreach ($useraccounts as $useraccount) {
            if ($useraccount->isSuperUser()) {
                $superusers[] = $useraccount->id;
            } else {
                $regularUsers[] = $useraccount->id;
            }
        }
        return [$superusers, $regularUsers];
    }

    protected function loadSuperuserDepartments(array $superusers, $resolveReferences = 0)
    {
        // Load all departments once - all superusers have access to all departments
            $query = Query\Useraccount::QUERY_READ_SUPERUSER_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query);
        $departmentList = $this->buildDepartmentList($departmentIds, $resolveReferences);

        // Reuse the same list for all superusers - no need to clone since they all get the same departments
        $result = [];
        foreach ($superusers as $useraccountName) {
            $result[$useraccountName] = $departmentList;
        }
        return $result;
    }

    protected function loadRegularUserDepartments(array $regularUsers, $resolveReferences = 0)
    {
        $placeholders = str_repeat('?,', count($regularUsers) - 1) . '?';
        $query = str_replace(':useraccountNames', $placeholders, Query\Useraccount::QUERY_READ_ASSIGNED_DEPARTMENTS_FOR_ALL);
        $allAssignments = $this->getReader()->fetchAll($query, $regularUsers);

        $assignmentsByUser = $this->groupAssignmentsByUser($allAssignments);
        return $this->buildDepartmentListsForUsers($regularUsers, $assignmentsByUser, $resolveReferences);
    }

    /**
     * Group department assignments by useraccount name
     *
     * @param array $allAssignments
     * @return array Map of useraccount name => assignments[]
     */
    protected function groupAssignmentsByUser(array $allAssignments)
    {
        $assignmentsByUser = [];
        foreach ($allAssignments as $assignment) {
            $useraccountName = $assignment['useraccountName'];
            if (!isset($assignmentsByUser[$useraccountName])) {
                $assignmentsByUser[$useraccountName] = [];
            }
            $assignmentsByUser[$useraccountName][] = $assignment;
        }
        return $assignmentsByUser;
    }

    protected function buildDepartmentListsForUsers(array $useraccountNames, array $assignmentsByUser, $resolveReferences = 0)
    {
        // Collect ALL unique department IDs from all useraccounts
        $allDepartmentIds = [];
        $departmentIdsByUser = [];

        foreach ($useraccountNames as $useraccountName) {
            $departmentIdsByUser[$useraccountName] = [];
            if (isset($assignmentsByUser[$useraccountName])) {
                foreach ($assignmentsByUser[$useraccountName] as $item) {
                    $departmentId = $item['id'];
                    if (!isset($allDepartmentIds[$departmentId])) {
                        $allDepartmentIds[$departmentId] = true;
                    }
                    $departmentIdsByUser[$useraccountName][] = $departmentId;
                }
            }
        }

        // Load ALL departments in ONE query
        $allDepartments = [];
        if (!empty($allDepartmentIds)) {
            $uniqueDepartmentIds = array_keys($allDepartmentIds);
            $allDepartments = (new \BO\Zmsdb\Department())->readEntitiesByIds($uniqueDepartmentIds, $resolveReferences);
        }

        // Build department lists for each useraccount from the pre-loaded departments
        $result = [];
        foreach ($useraccountNames as $useraccountName) {
            $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
            if (isset($departmentIdsByUser[$useraccountName])) {
                foreach ($departmentIdsByUser[$useraccountName] as $departmentId) {
                    if (isset($allDepartments[$departmentId])) {
                        // Clone department so each useraccount gets its own instance
                        $departmentList->addEntity(clone $allDepartments[$departmentId]);
                    }
                }
            }
            $result[$useraccountName] = $departmentList;
        }

        return $result;
    }

    protected function buildDepartmentList(array $items, $resolveReferences = 0)
    {
        $departmentList = new \BO\Zmsentities\Collection\DepartmentList();

        if (empty($items)) {
            return $departmentList;
        }

        $departmentIds = [];
        foreach ($items as $item) {
            $departmentIds[] = $item['id'];
        }

        $departments = (new \BO\Zmsdb\Department())->readEntitiesByIds($departmentIds, $resolveReferences);

        foreach ($departmentIds as $id) {
            if (isset($departments[$id])) {
                // Clone department so the list has its own instances
                $departmentList->addEntity(clone $departments[$id]);
            }
        }

        return $departmentList;
    }

    public function readEntityByAuthKey($xAuthKey, $resolveReferences = 0)
    {
        $hashedAuthKey = hash('sha256', $xAuthKey);
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionXauthKey($hashedAuthKey);
        $entity = ($hashedAuthKey) ? $this->fetchOne($query, new Entity()) : new Entity();
        return $this->readResolvedReferences($entity, $resolveReferences);
    }

    public function readEntityByUserId($userId, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionUserId($userId);
        $entity = ($userId) ? $this->fetchOne($query, new Entity()) : new Entity();
        return $this->readResolvedReferences($entity, $resolveReferences);
    }

    /**
     * @SuppressWarnings(NPathComplexity)
     */
    public function readCollectionByDepartmentIds($departmentIds, $resolveReferences = 0, $disableCache = false, $workstation = null)
    {
        sort($departmentIds);
        $version = $this->getUseraccountCacheVersion();
        $workstationKey = '';
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $workstationKey = '-workstation-' . $workstation->getUseraccount()->id;
        }
        $cacheKey = "useraccountReadByDepartmentIds-v{$version}-" . implode(',', $departmentIds) . "-$resolveReferences$workstationKey";
        $result = null;

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            $result = App::$cache->get($cacheKey);
            if ($result && App::$log) {
                App::$log->info('Useraccount department list cache hit', [
                    'cache_key' => $cacheKey,
                    'department_ids' => $departmentIds,
                    'resolveReferences' => $resolveReferences,
                    'count' => $result->count()
                ]);
            }
        }

        if (empty($result)) {
            $collection = new Collection();
            $query = new Query\Useraccount(Query\Base::SELECT);
            $query->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentIds($departmentIds)
            ->addEntityMapping();

            // Exclude superusers if workstation user is not superuser
            if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
                $query->addConditionExcludeSuperusers();
            }

            $result = $this->fetchList($query, new Entity());
            if (count($result)) {
                foreach ($result as $entity) {
                    $collection->addEntity($entity);
                }
                if (0 < $resolveReferences) {
                    $departmentMap = $this->readAssignedDepartmentListsForAll($collection, $resolveReferences - 1);
                    foreach ($collection as $entity) {
                        if (isset($departmentMap[$entity->id])) {
                            $entity->departments = $departmentMap[$entity->id];
                        }
                    }
                }
            }
            $result = $collection;

            if (App::$cache) {
                App::$cache->set($cacheKey, $result);
                $this->registerCacheKeyForDepartments($departmentIds, $cacheKey);
                if (App::$log) {
                    App::$log->info('Useraccount department list cache set', [
                        'cache_key' => $cacheKey,
                        'department_ids' => $departmentIds,
                        'resolveReferences' => $resolveReferences,
                        'count' => $result->count()
                    ]);
                }
            }
        }

        return $result;
    }

    public function writeEntity(\BO\Zmsentities\Useraccount $entity, $resolveReferences = 0)
    {
        if ($this->readIsUserExisting($entity->id)) {
            throw new Exception\Useraccount\DuplicateEntry();
        }
        $query = new Query\Useraccount(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->updateAssignedDepartments($entity);

        $this->removeCache($entity);

        return $this->readEntity($entity->getId(), $resolveReferences, true);
    }

    public function writeUpdatedEntity($loginName, \BO\Zmsentities\Useraccount $entity, $resolveReferences = 0)
    {
        $previousDepartmentIds = $this->readDepartmentIdsForLoginName($loginName);
        $query = new Query\Useraccount(Query\Base::UPDATE);
        $query->addConditionLoginName($loginName);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->updateAssignedDepartments($entity);

        $this->removeCache($entity, $previousDepartmentIds);

        return $this->readEntity($entity->getId(), $resolveReferences, true);
    }

    public function deleteEntity($loginName)
    {
        // Read entity before deletion to get cache info
        $entity = $this->readEntity($loginName, 0, true);
        $previousDepartmentIds = $this->readDepartmentIdsForLoginName($loginName);

        $query = new Query\Useraccount(Query\Base::DELETE);
        $query->addConditionLoginName($loginName);
        $this->deleteAssignedDepartments($loginName);
        $result = $this->deleteItem($query);

        if ($entity && $entity->hasId()) {
            $this->removeCache($entity, $previousDepartmentIds);
        }

        return $result;
    }

    protected function updateAssignedDepartments($entity)
    {
        $loginName = $entity->id;
        if (!$entity->isSuperUser()) {
            $this->deleteAssignedDepartments($loginName);
            $userId = $this->readEntityIdByLoginName($loginName);
            foreach ($entity->departments as $department) {
                $this->perform(
                    Query\Useraccount::QUERY_WRITE_ASSIGNED_DEPARTMENTS,
                    array(
                        $userId,
                        $department['id']
                    )
                );
            }
        }
    }

    protected function readEntityIdByLoginName($loginName)
    {
        $query = Query\Useraccount::QUERY_READ_ID_BY_USERNAME;
        $result = $this->getReader()->fetchOne($query, [$loginName]);
        return $result['id'];
    }

    protected function deleteAssignedDepartments($loginName)
    {
        $query = Query\Useraccount::QUERY_DELETE_ASSIGNED_DEPARTMENTS;
        $userId = $this->readEntityIdByLoginName($loginName);
        return $this->perform($query, [$userId]);
    }

    protected function buildSearchCacheKey($prefix, $resolveReferences, $workstation, $queryString, array $departmentIds = [])
    {
        $version = $this->getUseraccountCacheVersion();
        $workstationKey = '';
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $workstationKey = '-workstation-' . $workstation->getUseraccount()->id;
        }
        $departmentKey = empty($departmentIds) ? '' : '-' . implode(',', $departmentIds);
        return "{$prefix}-v{$version}{$departmentKey}-$resolveReferences$workstationKey-query-" . md5($queryString);
    }

    protected function getCachedResult($cacheKey, $disableCache, $logMessage, array $logContext = [])
    {
        $result = null;
        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            $result = App::$cache->get($cacheKey);
            if ($result && App::$log) {
                $logContext['cache_key'] = $cacheKey;
                $logContext['count'] = $result->count();
                App::$log->info($logMessage, $logContext);
            }
        }
        return $result;
    }

    protected function setCachedResult($cacheKey, $result, array $departmentIds, $logMessage, array $logContext = [])
    {
        if (App::$cache) {
            App::$cache->set($cacheKey, $result);
            $this->registerCacheKeyForDepartments($departmentIds, $cacheKey);
            if (App::$log) {
                $logContext['cache_key'] = $cacheKey;
                $logContext['count'] = $result->count();
                App::$log->info($logMessage, $logContext);
            }
        }
    }

    protected function executeSearchQuery(array $parameter, $resolveReferences, $workstation)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

        // For superusers: select all users without department filtering
        // For non-superusers: apply department-based access filtering
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $workstationUserId = $this->readEntityIdByLoginName($workstation->getUseraccount()->id);
            $workstationDepartmentIds = $workstation->getDepartmentList()->getIds();

            // If no departments loaded, return empty result for security
            if (empty($workstationDepartmentIds)) {
                return new Collection();
            }

            $query->addConditionWorkstationAccess(
                $workstationUserId,
                $workstationDepartmentIds,
                $workstation->getUseraccount()->isSuperUser()
            );
        }

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionUserId($parameter['query']);
                $query->addConditionSearch($parameter['query'], true);
            } else {
                $query->addConditionSearch($parameter['query']);
            }
        }

        $statement = $this->fetchStatement($query);
        return $this->readListStatement($statement, $resolveReferences);
    }

    protected function executeSearchByDepartmentIdsQuery(array $departmentIds, array $parameter, $resolveReferences, $workstation)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionUserId($parameter['query']);
                $query->addConditionDepartmentIdsAndSearch($departmentIds, $parameter['query'], true);
            } else {
                $query->addConditionDepartmentIdsAndSearch($departmentIds, $parameter['query']);
            }
        } else {
            $query->addConditionDepartmentIds($departmentIds);
        }

        // Exclude superusers if workstation user is not superuser
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $query->addConditionExcludeSuperusers();
        }

        $statement = $this->fetchStatement($query);
        return $this->readListStatement($statement, $resolveReferences);
    }

    /**
     * @SuppressWarnings(NPathComplexity)
     */
    public function readSearch(array $parameter, $resolveReferences = 0, $workstation = null, $disableCache = false)
    {
        $queryString = isset($parameter['query']) ? $parameter['query'] : '';
        $cacheKey = $this->buildSearchCacheKey('useraccountReadSearch', $resolveReferences, $workstation, $queryString);
        $result = $this->getCachedResult($cacheKey, $disableCache, 'Useraccount search cache hit', [
            'query' => $queryString,
            'resolveReferences' => $resolveReferences
        ]);

        if (empty($result)) {
            $result = $this->executeSearchQuery($parameter, $resolveReferences, $workstation);
            $this->setCachedResult($cacheKey, $result, [self::CACHE_INDEX_GLOBAL], 'Useraccount search cache set', [
                'query' => $queryString,
                'resolveReferences' => $resolveReferences
            ]);
        }

        return $result;
    }

    /**
     * @SuppressWarnings(NPathComplexity)
     */
    public function readSearchByDepartmentIds(array $departmentIds, array $parameter, $resolveReferences = 0, $workstation = null, $disableCache = false)
    {
        sort($departmentIds);
        $queryString = isset($parameter['query']) ? $parameter['query'] : '';
        $cacheKey = $this->buildSearchCacheKey('useraccountReadSearchByDepartmentIds', $resolveReferences, $workstation, $queryString, $departmentIds);
        $result = $this->getCachedResult($cacheKey, $disableCache, 'Useraccount search by department cache hit', [
            'department_ids' => $departmentIds,
            'query' => $queryString,
            'resolveReferences' => $resolveReferences
        ]);

        if (empty($result)) {
            $result = $this->executeSearchByDepartmentIdsQuery($departmentIds, $parameter, $resolveReferences, $workstation);
            $this->setCachedResult($cacheKey, $result, $departmentIds, 'Useraccount search by department cache set', [
                'department_ids' => $departmentIds,
                'query' => $queryString,
                'resolveReferences' => $resolveReferences
            ]);
        }

        return $result;
    }

    public function readListRole($roleLevel, $resolveReferences = 0, $workstation = null)
    {
        $version = $this->getUseraccountCacheVersion();
        $workstationKey = '';
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $workstationKey = '-workstation-' . $workstation->getUseraccount()->id;
        }
        $cacheKey = "useraccountReadByRole-v{$version}-" . ($roleLevel ?? 'null') . "-$resolveReferences$workstationKey";
        $result = null;

        if (App::$cache && App::$cache->has($cacheKey)) {
            $result = App::$cache->get($cacheKey);
            if ($result && App::$log) {
                App::$log->info('Useraccount role list cache hit', [
                    'cache_key' => $cacheKey,
                    'role_level' => $roleLevel,
                    'resolveReferences' => $resolveReferences,
                    'count' => $result->count()
                ]);
            }
        }

        if (empty($result)) {
            $query = new Query\Useraccount(Query\Base::SELECT);
            $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

            if (isset($roleLevel)) {
                $query->addConditionRoleLevel($roleLevel);
            }

            // Apply workstation access filtering if provided
            if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
                $workstationUserId = $this->readEntityIdByLoginName($workstation->getUseraccount()->id);
                $workstationDepartmentIds = $workstation->getDepartmentList()->getIds();
                $query->addConditionWorkstationAccess(
                    $workstationUserId,
                    $workstationDepartmentIds,
                    $workstation->getUseraccount()->isSuperUser()
                );
            }

            $statement = $this->fetchStatement($query);
            $result = $this->readListStatement($statement, $resolveReferences);

            if (App::$cache) {
                App::$cache->set($cacheKey, $result);
                $this->registerCacheKeyForDepartments([self::CACHE_INDEX_GLOBAL], $cacheKey);
                if (App::$log) {
                    App::$log->info('Useraccount role list cache set', [
                        'cache_key' => $cacheKey,
                        'role_level' => $roleLevel,
                        'resolveReferences' => $resolveReferences,
                        'count' => $result->count()
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * @SuppressWarnings(NPathComplexity)
     */
    public function readListByRoleAndDepartmentIds($roleLevel, array $departmentIds, $resolveReferences = 0, $disableCache = false, $workstation = null)
    {
        sort($departmentIds);
        $version = $this->getUseraccountCacheVersion();
        $workstationKey = '';
        if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
            $workstationKey = '-workstation-' . $workstation->getUseraccount()->id;
        }
        $cacheKey = "useraccountReadByRoleAndDepartmentIds-v{$version}-$roleLevel-" . implode(',', $departmentIds) . "-$resolveReferences$workstationKey";
        $result = null;

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            $result = App::$cache->get($cacheKey);
            if ($result && App::$log) {
                App::$log->info('Useraccount role and department list cache hit', [
                    'cache_key' => $cacheKey,
                    'role_level' => $roleLevel,
                    'department_ids' => $departmentIds,
                    'resolveReferences' => $resolveReferences,
                    'count' => $result->count()
                ]);
            }
        }

        if (empty($result)) {
            $query = new Query\Useraccount(Query\Base::SELECT);
            $query->addResolvedReferences($resolveReferences)
              ->addEntityMapping();

            if (isset($roleLevel) && !empty($departmentIds)) {
                $query->addConditionRoleLevel($roleLevel);
                $query->addConditionDepartmentIds($departmentIds);
            }

            // Exclude superusers if workstation user is not superuser
            if ($workstation && !$workstation->getUseraccount()->isSuperUser()) {
                $query->addConditionExcludeSuperusers();
            }

            $statement = $this->fetchStatement($query);
            $result = $this->readListStatement($statement, $resolveReferences);

            if (App::$cache) {
                App::$cache->set($cacheKey, $result);
                $this->registerCacheKeyForDepartments($departmentIds, $cacheKey);
                if (App::$log) {
                    App::$log->info('Useraccount role and department list cache set', [
                        'cache_key' => $cacheKey,
                        'role_level' => $roleLevel,
                        'department_ids' => $departmentIds,
                        'resolveReferences' => $resolveReferences,
                        'count' => $result->count()
                    ]);
                }
            }
        }

        return $result;
    }

    public function removeCache($useraccount, array $previousDepartmentIds = [])
    {
        if (!App::$cache) {
            return;
        }

        $currentVersion = $this->getUseraccountCacheVersion();
        $identifiers = $this->collectUseraccountIdentifiers($useraccount);
        $removedEntityKeys = [];

        foreach ($identifiers as $identifier) {
            for ($i = 0; $i <= 2; $i++) {
                $cacheKey = $this->sanitizeCacheKey("useraccount-v{$currentVersion}-{$identifier}-$i");
                if ($this->deleteCacheKey($cacheKey)) {
                    $removedEntityKeys[] = $cacheKey;
                }
            }
        }

        $departmentIds = $this->collectDepartmentIdsForInvalidation($useraccount, $previousDepartmentIds);
        $removedDepartmentCaches = $this->invalidateDepartmentCaches($departmentIds);

        $versionBumped = false;
        $newVersion = $currentVersion;

        if (!$removedDepartmentCaches) {
            $newVersion = $currentVersion + 1;
            App::$cache->set(self::CACHE_VERSION_KEY, $newVersion);
            $versionBumped = true;
        }

        if (App::$log) {
            App::$log->info('Useraccount caches invalidated after mutation', [
                'useraccount_id' => $useraccount->id ?? null,
                'identifiers' => $identifiers,
                'removed_entity_cache_keys' => $removedEntityKeys,
                'department_ids' => $departmentIds,
                'version_bumped' => $versionBumped,
                'old_version' => $currentVersion,
                'new_version' => $newVersion,
            ]);
        }
    }
}
