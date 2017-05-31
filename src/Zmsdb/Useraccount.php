<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Useraccount as Entity;
use \BO\Zmsentities\Collection\UseraccountList as Collection;

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
            ->addConditionLoginName($loginName);
        if ($password) {
            $query->addConditionPassword($password);
        }
        $userAccount = $this->fetchOne($query, new Entity());
        return ($userAccount->hasId()) ? true : false;
    }

    public function readEntity($loginname, $resolveReferences = 1)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionLoginName($loginname);
        $userAccount = $this->fetchOne($query, new Entity());
        return $this->readResolvedReferences($userAccount, $resolveReferences);
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $userAccount, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            if ($userAccount->toProperty()->id->get()) {
                $userAccount->departments = $this->readAssignedDepartmentList($userAccount, $resolveReferences);
            }
        }
        return $userAccount;
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
                if ($entity instanceof Entity) {
                    $entity->departments = $this->readAssignedDepartmentList($entity, $resolveReferences);
                    $collection->addEntity($entity);
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
    public function readAssignedDepartmentList($userAccount, $resolveReferences = 0)
    {
        if ($userAccount->isSuperUser()) {
            $query = Query\Useraccount::QUERY_READ_SUPERUSER_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query);
        } else {
            $query = Query\Useraccount::QUERY_READ_ASSIGNED_DEPARTMENTS;
            $departmentIds = $this->getReader()->fetchAll($query, ['userAccountName' => $userAccount->id]);
        }
        $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
        foreach ($departmentIds as $item) {
            $department = (new \BO\Zmsdb\Department())->readEntity($item['id'], $resolveReferences);
            if ($department instanceof \BO\Zmsentities\Department && 0 < $department->getScopeList()->count()) {
                $department->name = $item['organisation__name'] .' -> '. $department->name;
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
        return ($xAuthKey) ? $this->fetchOne($query, new Entity()) : new Entity();
    }

    public function readEntityByUserId($userId, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionUserId($userId);
        return ($userId) ? $this->fetchOne($query, new Entity()) : new Entity();
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
                if ($entity instanceof Entity) {
                    $entity->departments = $this->readAssignedDepartmentList($entity, $resolveReferences - 1);
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    /**
     * write an userAccount
     *
     * @param
     *            entity
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Useraccount $entity, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::INSERT);
        if ($this->readIsUserExisting($entity->id)) {
            $userAccount = $this->updateEntity($entity->id, $entity);
        } else {
            $values = $query->reverseEntityMapping($entity);
            $query->addValues($values);
            $this->writeItem($query);
            $this->updateAssignedDepartments($entity);
            $userAccount = $this->readEntity($entity->id, $resolveReferences);
        }
        return $userAccount;
    }

    /**
     * update a userAccount
     *
     * @param
     *            userAccountId
     *
     * @return Entity
     */
    public function updateEntity($loginName, \BO\Zmsentities\Useraccount $entity, $resolveReferences = 0)
    {
        $query = new Query\Useraccount(Query\Base::UPDATE);
        $query->addConditionLoginName($loginName);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->updateAssignedDepartments($entity);
        $newLoginName = $loginName !== $entity->id ? $entity->id : $loginName;
        return $this->readEntity($newLoginName, $resolveReferences);
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
        $this->deleteAssignedDepartments($loginName);
        if (! $entity->isSuperUser()) {
            $query = Query\Useraccount::QUERY_WRITE_ASSIGNED_DEPARTMENTS;
            $statement = $this->getWriter()->prepare($query);
            $userId = $this->readEntityIdByLoginName($loginName);
            foreach ($entity->departments as $department) {
                $statement->execute(
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
        $statement = $this->getWriter()->prepare($query);
        $userId = $this->readEntityIdByLoginName($loginName);
        $statement->execute([$userId]);
    }
}
