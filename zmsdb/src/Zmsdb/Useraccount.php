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
            // TODO subtract -1 from resolveReference, but check calling functions!
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
            foreach ($result as $entity) {
                $collection->addEntity($this->readResolvedReferences($entity, $resolveReferences));
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
            $entity = $this->readResolvedReferences($entity, $resolveReferences);
            $collection->addEntity($entity);
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

    public function readEntityByAuthKey($xAuthKey, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionXauthKey($xAuthKey);
        $entity = ($xAuthKey) ? $this->fetchOne($query, new Entity()) : new Entity();
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
                $collection->addEntity($this->readResolvedReferences($entity, $resolveReferences));
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
