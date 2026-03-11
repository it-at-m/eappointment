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
     *
     * Note: This method uses a non-atomic read-modify-write pattern which may
     * experience race conditions under high concurrency. Lost index entries are
     * handled by the version-bump fallback in removeCache(), ensuring eventual
     * consistency at the cost of a full cache invalidation.
     *
     * @see https://github.com/it-at-m/eappointment/issues/1804
     *      Migration to Redis for atomic operations is tracked in issue #1804.
     *
     * @param array $departmentIds Department IDs to associate with the cache key
     * @param string $cacheKey The cache key to register
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

    protected function readPermissionNamesForUserId(int $userId): array
    {
        $sql = '
            SELECT DISTINCT p.name
            FROM user_role ur
            INNER JOIN role_permission rp ON rp.role_id = ur.role_id
            INNER JOIN permission p ON p.id = rp.permission_id
            WHERE ur.user_id = :userId
        ';

        $rows = $this->getReader()->fetchAll($sql, ['userId' => $userId]);
        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        return array_values(array_unique(array_column($rows, 'name')));
    }

    protected function readRoleNamesForUserId(int $userId): array
    {
        $sql = '
            SELECT DISTINCT r.name
            FROM user_role ur
            INNER JOIN role r ON r.id = ur.role_id
            WHERE ur.user_id = :userId
        ';

        $rows = $this->getReader()->fetchAll($sql, ['userId' => $userId]);
        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        return array_values(array_unique(array_column($rows, 'name')));
    }

    protected function hydratePermissionsForUseraccount(Entity $useraccount): Entity
    {
        if (!$useraccount->hasId()) {
            return $useraccount;
        }

        $userId = $this->readEntityIdByLoginName($useraccount->id);
        $permissionNames = $this->readPermissionNamesForUserId((int) $userId);

        if (empty($permissionNames)) {
            return $useraccount;
        }

        foreach ($permissionNames as $permission) {
            if (isset($useraccount->permissions) && array_key_exists($permission, $useraccount->permissions)) {
                $useraccount->permissions[$permission] = true;
            }
        }

        // Superusers implicitly have all permissions; ensure the boolean map reflects that
        if ($useraccount->isSuperUser() && isset($useraccount->permissions) && is_array($useraccount->permissions)) {
            foreach (array_keys($useraccount->permissions) as $permissionKey) {
                $useraccount->permissions[$permissionKey] = true;
            }
        }

        return $useraccount;
    }

    protected function hydrateRolesForUseraccount(Entity $useraccount): Entity
    {
        if (!$useraccount->hasId()) {
            return $useraccount;
        }

        $userId = $this->readEntityIdByLoginName($useraccount->id);
        $roleNames = $this->readRoleNamesForUserId((int) $userId);

        if (empty($roleNames)) {
            return $useraccount;
        }

        $useraccount->roles = $roleNames;

        return $useraccount;
    }

    protected function hydratePermissionsForCollection(Collection $useraccounts): Collection
    {
        if (count($useraccounts) === 0) {
            return $useraccounts;
        }

        $names = [];
        foreach ($useraccounts as $entity) {
            if (isset($entity->id)) {
                $names[] = $entity->id;
            }
        }
        $names = array_values(array_unique(array_filter($names)));

        if (empty($names)) {
            return $useraccounts;
        }

        $ids = [];
        foreach ($names as $loginName) {
            $ids[$loginName] = $this->readEntityIdByLoginName($loginName);
        }

        if (empty($ids)) {
            return $useraccounts;
        }

        $allPermissions = [];
        $sql = '
            SELECT ur.user_id, p.name AS permission
            FROM user_role ur
            INNER JOIN role_permission rp ON rp.role_id = ur.role_id
            INNER JOIN permission p ON p.id = rp.permission_id
            WHERE ur.user_id IN (:userIds)
        ';
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = str_replace(':userIds', $placeholders, $sql);
        $rows = $this->getReader()->fetchAll($sql, array_values($ids));

        foreach ($rows as $row) {
            $userId = $row['user_id'];
            $permission = $row['permission'];
            if (!isset($allPermissions[$userId])) {
                $allPermissions[$userId] = [];
            }
            $allPermissions[$userId][] = $permission;
        }

        foreach ($useraccounts as $entity) {
            $loginName = $entity->id ?? null;
            if ($loginName === null || !isset($ids[$loginName])) {
                continue;
            }
            $userId = $ids[$loginName];
            if (!isset($allPermissions[$userId])) {
                continue;
            }
            foreach (array_unique($allPermissions[$userId]) as $permission) {
                if (isset($entity->permissions) && array_key_exists($permission, $entity->permissions)) {
                    $entity->permissions[$permission] = true;
                }
            }

            // Superusers implicitly have all permissions; ensure the boolean map reflects that
            if ($entity->isSuperUser() && isset($entity->permissions) && is_array($entity->permissions)) {
                foreach (array_keys($entity->permissions) as $permissionKey) {
                    $entity->permissions[$permissionKey] = true;
                }
            }
        }

        return $useraccounts;
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

    protected function applyWorkstationAccessFilter(Query\Useraccount $query, $workstation): bool
    {
        if (!$workstation || $workstation->getUseraccount()->isSuperUser()) {
            return true; // No filtering needed for superusers
        }

        $workstationUserId = $this->readEntityIdByLoginName($workstation->getUseraccount()->id);
        $workstationDepartmentIds = $workstation->getDepartmentList()->getIds();

        // If no departments loaded, return empty result for security
        if (empty($workstationDepartmentIds)) {
            return false; // Signal to return empty collection
        }

        $query->addConditionWorkstationAccess(
            $workstationUserId,
            $workstationDepartmentIds,
            false // We already checked isSuperUser above
        );

        return true;
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

            // First hydrate permissions and roles so isSuperUser() and permission-based
            // logic are available when resolving references such as departments.
            $useraccount = $this->hydratePermissionsForUseraccount($useraccount);
            $useraccount = $this->hydrateRolesForUseraccount($useraccount);
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
            ->addEntityMapping()
            ->addOrderByName();

            // Apply workstation access filtering if provided
            if (!$this->applyWorkstationAccessFilter($query, $workstation)) {
                $result = new Collection();
                return $result;
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

            $result = $this->hydratePermissionsForCollection($result);

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

        if ($entity->hasId()) {
            // Ensure permissions and roles are hydrated so that isSuperUser() and
            // permission-based guards work correctly for X-AuthKey authenticated users.
            $entity = $this->hydratePermissionsForUseraccount($entity);
            $entity = $this->hydrateRolesForUseraccount($entity);
            $entity = $this->readResolvedReferences($entity, $resolveReferences);
        }

        return $entity;
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
            ->addEntityMapping()
            ->addOrderByName();

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
        $this->updateUserRoles($entity);

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
        $this->updateUserRoles($entity);

        $this->removeCache($entity, $previousDepartmentIds, $loginName);

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
            $this->removeCache($entity, $previousDepartmentIds, $loginName);
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

    protected function updateUserRoles(\BO\Zmsentities\Useraccount $entity): void
    {
        $loginName = $entity->id;
        $userId = $this->readEntityIdByLoginName($loginName);

        // Clear existing role assignments for this user
        $this->perform('DELETE FROM user_role WHERE user_id = ?', [$userId]);

        // Prefer explicit roles on the entity when present (new model)
        if (isset($entity->roles) && is_array($entity->roles) && !empty($entity->roles)) {
            $sql = '
                INSERT IGNORE INTO user_role (user_id, role_id)
                SELECT :userId, r.id
                FROM role r
                WHERE r.name = :roleName
            ';

            foreach ($entity->roles as $roleName) {
                if (!is_string($roleName) || $roleName === '') {
                    continue;
                }
                $this->perform($sql, [
                    'userId' => $userId,
                    'roleName' => $roleName,
                ]);
            }

            return;
        }

        // Fallback for legacy flows: derive a single canonical role from Berechtigung
        $berechtigung = $entity->getRightsLevel();
        $roleName = null;

        switch ($berechtigung) {
            case 90:
                $roleName = 'system_admin';
                break;
            case 40:
                $roleName = 'user_admin';
                break;
            case 30:
                $roleName = 'appointment_admin';
                break;
            case 5:
                $roleName = 'audit_viewer';
                break;
            case 0:
                $roleName = 'agent_queue';
                break;
            default:
                $roleName = null;
        }

        if (null === $roleName) {
            return;
        }

        $sql = '
            INSERT IGNORE INTO user_role (user_id, role_id)
            SELECT :userId, r.id
            FROM role r
            WHERE r.name = :roleName
        ';

        $this->perform($sql, [
            'userId' => $userId,
            'roleName' => $roleName,
        ]);
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
            ->addEntityMapping()
            ->addOrderByName();

        // For superusers: select all users without department filtering
        // For non-superusers: apply department-based access filtering
        if (!$this->applyWorkstationAccessFilter($query, $workstation)) {
            return new Collection();
        }

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionUserId($parameter['query']);
                $query->addConditionSearch($parameter['query'], true);
            } else {
                $query->addConditionSearch($parameter['query']);
            }
        }

        $collection = new Collection();
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
        $collection = $this->hydratePermissionsForCollection($collection);

        return $collection;
    }

    protected function executeSearchByDepartmentIdsQuery(array $departmentIds, array $parameter, $resolveReferences, $workstation)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addOrderByName();

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

        $collection = new Collection();
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
        $collection = $this->hydratePermissionsForCollection($collection);

        return $collection;
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
        // Legacy numeric role levels are no longer supported; this method is kept
        // only for backwards compatibility of the public API and now delegates
        // to a role-based implementation using the permissions / roles model.
        $roleName = $this->mapLegacyRoleLevelToRoleName($roleLevel);
        if (null === $roleName) {
            return new Collection();
        }

        // Note: $workstation is currently ignored here; this method is kept
        // for backwards compatibility and delegates to a role-based helper.
        return $this->readListByRoleNames([$roleName], $resolveReferences);
    }

    /**
     * @SuppressWarnings(NPathComplexity)
     */
    public function readListByRoleAndDepartmentIds($roleLevel, array $departmentIds, $resolveReferences = 0, $disableCache = false, $workstation = null)
    {
        $roleName = $this->mapLegacyRoleLevelToRoleName($roleLevel);
        if (null === $roleName || empty($departmentIds)) {
            return new Collection();
        }

        // Note: $disableCache and $workstation are currently ignored here; this
        // method is kept for backwards compatible public API surface.
        return $this->readListByRoleNamesAndDepartmentIds([$roleName], $departmentIds, $resolveReferences);
    }

    /**
     * Map legacy numeric Berechtigung levels to canonical role names.
     * Kept for backwards compatible public APIs that still receive numeric levels.
     */
    private function mapLegacyRoleLevelToRoleName($roleLevel): ?string
    {
        switch ((int) $roleLevel) {
            case 90:
                return 'system_admin';
            case 40:
                return 'user_admin';
            case 30:
                return 'appointment_admin';
            case 5:
                return 'audit_viewer';
            case 0:
                return 'agent_queue';
            default:
                return null;
        }
    }

    /**
     * Read useraccounts that have at least one of the given role names.
     */
    private function readListByRoleNames(array $roleNames, int $resolveReferences = 0): Collection
    {
        $roleNames = array_values(array_unique(array_filter($roleNames, 'strlen')));
        $collection = new Collection();

        if (empty($roleNames)) {
            return $collection;
        }

        $reader = $this->getReader();
        $placeholders = implode(',', array_fill(0, count($roleNames), '?'));
        $sql = '
            SELECT DISTINCT useraccount.Name
            FROM ' . Query\Useraccount::TABLE . ' useraccount
            INNER JOIN user_role ur ON ur.user_id = useraccount.NutzerID
            INNER JOIN role r ON r.id = ur.role_id
            WHERE r.name IN (' . $placeholders . ')
        ';

        $rows = $reader->fetchAll($sql, $roleNames);
        if (!is_array($rows) || empty($rows)) {
            return $collection;
        }

        foreach ($rows as $row) {
            $entity = $this->readEntity($row['Name'], $resolveReferences, true);
            if ($entity instanceof Entity && $entity->hasId()) {
                $collection->addEntity($entity);
            }
        }

        return $collection;
    }

    /**
     * Read useraccounts that have at least one of the given role names and belong to the given departments.
     */
    private function readListByRoleNamesAndDepartmentIds(array $roleNames, array $departmentIds, int $resolveReferences = 0): Collection
    {
        $roleNames = array_values(array_unique(array_filter($roleNames, 'strlen')));
        $departmentIds = array_values(array_unique(array_filter($departmentIds, function ($id) {
            return $id !== null && $id !== '';
        })));

        $collection = new Collection();

        if (empty($roleNames) || empty($departmentIds)) {
            return $collection;
        }

        $reader = $this->getReader();

        $rolePlaceholders = implode(',', array_fill(0, count($roleNames), '?'));
        $departmentPlaceholders = implode(',', array_fill(0, count($departmentIds), '?'));

        $sql = '
            SELECT DISTINCT useraccount.Name
            FROM ' . Query\Useraccount::TABLE . ' useraccount
            INNER JOIN user_role ur ON ur.user_id = useraccount.NutzerID
            INNER JOIN role r ON r.id = ur.role_id
            INNER JOIN ' . Query\Useraccount::TABLE_ASSIGNMENT . ' useraccount_department
                ON useraccount_department.nutzerid = useraccount.NutzerID
            WHERE r.name IN (' . $rolePlaceholders . ')
              AND useraccount_department.behoerdenid IN (' . $departmentPlaceholders . ')
        ';

        $params = array_merge($roleNames, $departmentIds);
        $rows = $reader->fetchAll($sql, $params);

        if (!is_array($rows) || empty($rows)) {
            return $collection;
        }

        foreach ($rows as $row) {
            $entity = $this->readEntity($row['Name'], $resolveReferences, true);
            if ($entity instanceof Entity && $entity->hasId()) {
                $collection->addEntity($entity);
            }
        }

        return $collection;
    }

    public function removeCache($useraccount, array $previousDepartmentIds = [], ?string $oldLoginName = null)
    {
        if (!App::$cache) {
            return;
        }

        $currentVersion = $this->getUseraccountCacheVersion();
        $identifiers = $this->collectUseraccountIdentifiers($useraccount);
        // Add old loginname to identifiers if provided and not already in the list
        // This ensures cache keys for the old loginname are invalidated when loginname changes
        if ($oldLoginName !== null && !in_array($oldLoginName, $identifiers, true)) {
            $identifiers[] = $oldLoginName;
        }
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
