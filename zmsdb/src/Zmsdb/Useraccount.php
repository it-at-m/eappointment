<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Useraccount as Entity;
use BO\Zmsentities\Collection\UseraccountList as Collection;

/**
 * @SuppressWarnings(Public)
 * @SuppressWarnings(TooManyMethods)
 *
 */
class Useraccount extends Base
{
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

    public function readEntity($loginname, $resolveReferences = 1)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionLoginName($loginname);
        $useraccount = $this->fetchOne($query, new Entity());
        return $this->readResolvedReferences($useraccount, $resolveReferences);
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $useraccount, $resolveReferences)
    {
        if (0 < $resolveReferences && $useraccount->toProperty()->id->get()) {
            $useraccount->departments = $this->readAssignedDepartmentList($useraccount, $resolveReferences);
        }
        return $useraccount;
    }

    public function readList($resolveReferences = 0)
    {
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
        return $collection;
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

    public function readCollectionByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId)
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
        return $collection;
    }

    public function readCollectionByDepartmentIds($departmentIds, $resolveReferences = 0)
    {
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
        return $collection;
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
        return $this->readEntity($entity->getId(), $resolveReferences);
    }

    public function writeUpdatedEntity($loginName, \BO\Zmsentities\Useraccount $entity, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::UPDATE);
        $query->addConditionLoginName($loginName);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->updateAssignedDepartments($entity);
        return $this->readEntity($entity->getId(), $resolveReferences);
    }

    public function deleteEntity($loginName)
    {
        $query = new Query\Useraccount(Query\Base::DELETE);
        $query->addConditionLoginName($loginName);
        $this->deleteAssignedDepartments($loginName);
        return $this->deleteItem($query);
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

    public function readListByRoleAndDepartment($roleLevel, $departmentId, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
              ->addEntityMapping();

        if (isset($roleLevel) && isset($departmentId)) {
            $query->addConditionRoleLevel($roleLevel);
            $query->addConditionDepartmentId($departmentId);
        }

        $statement = $this->fetchStatement($query);
        return $this->readListStatement($statement, $resolveReferences);
    }
}
