<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Useraccount as Entity;
use BO\Zmsentities\Collection\UseraccountList as Collection;

/**
 * @SuppressWarnings(Public)
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

    /**
     * read list of useraccounts
     *
     * @param
     *            resolveReferences
     *
     * @return Resource Collection
     */
    public function readList($resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            // First collect all entities without resolving references
            foreach ($result as $entity) {
                $collection->addEntity($entity);
            }
            // Then batch load departments for all entities at once
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
        // First collect all entities without resolving references
        while ($userAccountData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcessJoins($userAccountData));
            $collection->addEntity($entity);
        }
        // Then batch load departments for all entities at once
        if (0 < $resolveReferences && count($collection) > 0) {
            $departmentMap = $this->readAssignedDepartmentListBatch($collection, $resolveReferences - 1);
            foreach ($collection as $entity) {
                if (isset($departmentMap[$entity->id])) {
                    $entity->departments = $departmentMap[$entity->id];
                }
            }
        }
        return $collection;
    }

    /**
     * read list assigned departments
     *
     * @param
     *            resolveReferences
     *
     * @return Resource Collection
     */
    public function readAssignedDepartmentList($useraccount, $resolveReferences = 0)
    {
        if ($useraccount->isSuperUser()) {
            $query = Query\Useraccount::QUERY_READ_SUPERUSER_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query);
        } else {
            $query = Query\Useraccount::QUERY_READ_ASSIGNED_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query, ['useraccountName' => $useraccount->id]);
        }
        $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
        foreach ($departmentIds as $item) {
            $department = (new \BO\Zmsdb\Department())->readEntity($item['id'], $resolveReferences);
            if ($department instanceof \BO\Zmsentities\Department) {
                $department->name = $item['organisation__name'] . ' -> ' . $department->name;
                $departmentList->addEntity($department);
            }
        }
        return $departmentList;
    }

    /**
     * Load assigned departments for all useraccounts in a single query
     *
     * @param Collection $useraccounts Collection of useraccount entities
     * @param int $resolveReferences
     * @return array Map of useraccount name => DepartmentList
     */
    protected function readAssignedDepartmentListsForAll(Collection $useraccounts, $resolveReferences = 0)
    {
        if (count($useraccounts) === 0) {
            return [];
        }

        // Separate superusers from regular users
        $superusers = [];
        $regularUsers = [];
        foreach ($useraccounts as $useraccount) {
            if ($useraccount->isSuperUser()) {
                $superusers[] = $useraccount->id;
            } else {
                $regularUsers[] = $useraccount->id;
            }
        }

        $result = [];

        // Load all departments once for all superusers
        if (count($superusers) > 0) {
            $query = Query\Useraccount::QUERY_READ_SUPERUSER_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query);
            $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
            foreach ($departmentIds as $item) {
                $department = (new \BO\Zmsdb\Department())->readEntity($item['id'], $resolveReferences);
                if ($department instanceof \BO\Zmsentities\Department) {
                    $department->name = $item['organisation__name'] . ' -> ' . $department->name;
                    $departmentList->addEntity($department);
                }
            }
            // Assign same department list to all superusers
            foreach ($superusers as $useraccountName) {
                $result[$useraccountName] = clone $departmentList;
            }
        }

            // Load all departments for regular users in one query
        if (count($regularUsers) > 0) {
            $placeholders = str_repeat('?,', count($regularUsers) - 1) . '?';
            $query = str_replace(':useraccountNames', $placeholders, Query\Useraccount::QUERY_READ_ASSIGNED_DEPARTMENTS_FOR_ALL);
            $allAssignments = $this->getReader()->fetchAll($query, $regularUsers);

            // Group assignments by useraccount name
            $assignmentsByUser = [];
            foreach ($allAssignments as $assignment) {
                $useraccountName = $assignment['useraccountName'];
                if (!isset($assignmentsByUser[$useraccountName])) {
                    $assignmentsByUser[$useraccountName] = [];
                }
                $assignmentsByUser[$useraccountName][] = $assignment;
            }

            // Build department lists for each useraccount
            foreach ($regularUsers as $useraccountName) {
                $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
                if (isset($assignmentsByUser[$useraccountName])) {
                    foreach ($assignmentsByUser[$useraccountName] as $item) {
                        $department = (new \BO\Zmsdb\Department())->readEntity($item['id'], $resolveReferences);
                        if ($department instanceof \BO\Zmsentities\Department) {
                            $department->name = $item['organisation__name'] . ' -> ' . $department->name;
                            $departmentList->addEntity($department);
                        }
                    }
                }
                $result[$useraccountName] = $departmentList;
            }
        }

        return $result;
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
            // First collect all entities without resolving references
            foreach ($result as $entity) {
                $collection->addEntity($entity);
            }
            // Then batch load departments for all entities at once
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
            // First collect all entities without resolving references
            foreach ($result as $entity) {
                $collection->addEntity($entity);
            }
            // Then batch load departments for all entities at once
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

    /**
     * write an useraccount
     *
     * @param
     *            entity
     *
     * @return Entity
     */
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

    /**
     * update a useraccount
     *
     * @param
     *            useraccountId
     *
     * @return Entity
     */
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

    /**
     * remove an user
     *
     * @param
     *            itemId
     *
     * @return Resource Status
     */
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

    public function readSearchByDepartmentId($departmentId, array $parameter, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addEntityMapping();

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionUserId($parameter['query']);
                $query->addConditionDepartmentAndSearch($departmentId, $parameter['query'], true);
            } else {
                $query->addConditionDepartmentAndSearch($departmentId, $parameter['query']);
            }
            unset($parameter['query']);
        } else {
            $query->addConditionDepartmentId($departmentId);
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
