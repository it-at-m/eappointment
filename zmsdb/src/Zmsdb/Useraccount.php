<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Useraccount as Entity;
use BO\Zmsentities\Collection\UseraccountList as Collection;

/**
 * @SuppressWarnings(Public)
 * @SuppressWarnings(TooManyMethods)
 *
 */
class Useraccount extends Base
{
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
        $cacheKey = $this->sanitizeCacheKey("useraccount-$loginname-$resolveReferences");

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

    public function readList($resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "useraccountReadList-$resolveReferences";

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
            $query = Query\Useraccount::QUERY_READ_SUPERUSER_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query);
        $departmentList = $this->buildDepartmentList($departmentIds, $resolveReferences);

        $result = [];
        foreach ($superusers as $useraccountName) {
            $result[$useraccountName] = clone $departmentList;
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
        // Collect ALL unique department IDs and organization names from all useraccounts
        $allDepartmentIds = [];
        $allOrganisationNameMap = [];
        $departmentIdsByUser = [];

        foreach ($useraccountNames as $useraccountName) {
            $departmentIdsByUser[$useraccountName] = [];
            if (isset($assignmentsByUser[$useraccountName])) {
                foreach ($assignmentsByUser[$useraccountName] as $item) {
                    $deptId = $item['id'];
                    if (!isset($allDepartmentIds[$deptId])) {
                        $allDepartmentIds[$deptId] = true;
                        $allOrganisationNameMap[$deptId] = $item['organisation__name'];
                    }
                    $departmentIdsByUser[$useraccountName][] = $deptId;
                }
            }
        }

        // Load ALL departments in ONE query
        $allDepartments = [];
        if (!empty($allDepartmentIds)) {
            $uniqueDepartmentIds = array_keys($allDepartmentIds);
            $allDepartments = (new \BO\Zmsdb\Department())->readEntitiesByIds($uniqueDepartmentIds, $resolveReferences);

            // Apply organization name prefix to all departments
            foreach ($allDepartments as $id => $department) {
                if (isset($allOrganisationNameMap[$id])) {
                    $department->name = $allOrganisationNameMap[$id] . ' -> ' . $department->name;
                }
            }
        }

        // Build department lists for each useraccount from the pre-loaded departments
        $result = [];
        foreach ($useraccountNames as $useraccountName) {
            $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
            if (isset($departmentIdsByUser[$useraccountName])) {
                foreach ($departmentIdsByUser[$useraccountName] as $deptId) {
                    if (isset($allDepartments[$deptId])) {
                        // Clone department so each useraccount gets its own instance
                        $departmentList->addEntity(clone $allDepartments[$deptId]);
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
        $organisationNameMap = [];
        foreach ($items as $item) {
            $departmentIds[] = $item['id'];
            $organisationNameMap[$item['id']] = $item['organisation__name'];
        }

        $departments = (new \BO\Zmsdb\Department())->readEntitiesByIds($departmentIds, $resolveReferences);

        foreach ($departmentIds as $id) {
            if (isset($departments[$id])) {
                $department = $departments[$id];
                $department->name = $organisationNameMap[$id] . ' -> ' . $department->name;
                $departmentList->addEntity($department);
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

    public function readCollectionByDepartmentIds($departmentIds, $resolveReferences = 0, $disableCache = false)
    {
        sort($departmentIds);
        $cacheKey = "useraccountReadByDepartmentIds-" . implode(',', $departmentIds) . "-$resolveReferences";

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
        $query = new Query\Useraccount(Query\Base::UPDATE);
        $query->addConditionLoginName($loginName);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->updateAssignedDepartments($entity);

        $this->removeCache($entity);

        return $this->readEntity($entity->getId(), $resolveReferences, true);
    }

    public function deleteEntity($loginName)
    {
        // Read entity before deletion to get cache info
        $entity = $this->readEntity($loginName, 0, true);

        $query = new Query\Useraccount(Query\Base::DELETE);
        $query->addConditionLoginName($loginName);
        $this->deleteAssignedDepartments($loginName);
        $result = $this->deleteItem($query);

        if ($entity && $entity->hasId()) {
            $this->removeCache($entity);
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

    public function readSearch(array $parameter, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionUserId($parameter['query']);
                $query->addConditionSearch($parameter['query'], true);
            } else {
                $query->addConditionSearch($parameter['query']);
            }
            unset($parameter['query']);
        }

        $statement = $this->fetchStatement($query);
        return $this->readListStatement($statement, $resolveReferences);
    }

    public function readSearchByDepartmentIds(array $departmentIds, array $parameter, $resolveReferences = 0)
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
            unset($parameter['query']);
        } else {
            $query->addConditionDepartmentIds($departmentIds);
        }

        $statement = $this->fetchStatement($query);
        return $this->readListStatement($statement, $resolveReferences);
    }

    public function readListRole($roleLevel, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

        if (isset($roleLevel)) {
            $query->addConditionRoleLevel($roleLevel);
        }

        $statement = $this->fetchStatement($query);
        return $this->readListStatement($statement, $resolveReferences);
    }

    public function readListByRoleAndDepartmentIds($roleLevel, array $departmentIds, $resolveReferences = 0, $disableCache = false)
    {
        sort($departmentIds);
        $cacheKey = "useraccountReadByRoleAndDepartmentIds-$roleLevel-" . implode(',', $departmentIds) . "-$resolveReferences";

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

            $statement = $this->fetchStatement($query);
            $result = $this->readListStatement($statement, $resolveReferences);

            if (App::$cache) {
                App::$cache->set($cacheKey, $result);
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

    public function removeCache($useraccount)
    {
        if (!App::$cache) {
            return;
        }

        // Collect all possible identifiers for this useraccount (id, loginname, etc.)
        $identifiers = [];

        if (isset($useraccount->id)) {
            $identifiers[] = $useraccount->id;
        }

        if (isset($useraccount->loginname) && $useraccount->loginname !== $useraccount->id) {
            $identifiers[] = $useraccount->loginname;
        }

        // Remove individual useraccount cache entries
        foreach ($identifiers as $identifier) {
            for ($i = 0; $i <= 2; $i++) {
                $cacheKey = $this->sanitizeCacheKey("useraccount-{$identifier}-$i");
                if (App::$cache->has($cacheKey)) {
                    App::$cache->delete($cacheKey);
                }
            }
        }

        // Remove all list caches (any list could contain this useraccount)
        // Invalidate main list cache
        for ($i = 0; $i <= 2; $i++) {
            $cacheKey = "useraccountReadList-$i";
            if (App::$cache->has($cacheKey)) {
                App::$cache->delete($cacheKey);
            }
        }

        // Note: Department-based and role-based list caches (e.g.,
        // useraccountReadByDepartmentIds-*, useraccountReadByRoleAndDepartmentIds-*)
        // cannot be easily invalidated without tracking all possible combinations.
        // These caches will expire naturally based on TTL, or we could implement
        // a cache tag/version system for more sophisticated invalidation.
    }
}
