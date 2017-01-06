<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Useraccount as Entity;
use \BO\Zmsentities\Collection\UseraccountList as Collection;

class UserAccount extends Base
{

    public function readIsUserExisting($loginName, $password = false)
    {
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionLoginName($loginName);
        if ($password) {
            $query->addConditionPassword($password);
        }
        $userAccount = $this->fetchOne($query, new Entity());
        return ($userAccount->hasId()) ? true : false;
    }

    public function readEntity($loginname, $resolveReferences = 0)
    {
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionLoginName($loginname);
        $userAccount = $this->fetchOne($query, new Entity());
        if ($userAccount->toProperty()->id->get()) {
            $userAccount->departments = $this->readAssignedDepartmentList($userAccount, $resolveReferences);
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
        $query = new Query\UserAccount(Query\Base::SELECT);
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
        $query = Query\UserAccount::QUERY_READ_ASSIGNED_DEPARTMENTS;
        $departmentIds = $this->getReader()
            ->fetchAll($query, [
            'userAccountName' => $userAccount->id
            ]);
        $checkFirstDepartment = reset($departmentIds);
        $departmentList = (0 == $checkFirstDepartment['id']) ? (new \BO\Zmsdb\Department())->readList() : null;

        if (count($departmentIds) && !$departmentList && 0 < $resolveReferences) {
            $departmentList = new \BO\Zmsentities\Collection\DepartmentList();
            foreach ($departmentIds as $item) {
                $department = (new \BO\Zmsdb\Department())->readEntity($item['id'], $resolveReferences);
                $departmentList->addEntity($department);
            }
        }
        return $departmentList;
    }

    public function readEntityByAuthKey($xAuthKey, $resolveReferences = 0)
    {
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionXauthKey($xAuthKey);
        return ($xAuthKey) ? $this->fetchOne($query, new Entity()) : new Entity();
    }

    /**
     * write an userAccount
     *
     * @param
     *            entity
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Useraccount $entity)
    {
        $query = new Query\UserAccount(Query\Base::INSERT);
        if ($this->readIsUserExisting($entity->id)) {
            $userAccount = $this->updateEntity($entity->id, $entity);
        } else {
            $values = $query->reverseEntityMapping($entity);
            $query->addValues($values);
            $this->writeItem($query);
            $this->updateAssignedDepartments($entity);
            $userAccount = $this->readEntity($entity->id, 1);
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
    public function updateEntity($loginName, \BO\Zmsentities\Useraccount $entity)
    {
        $query = new Query\UserAccount(Query\Base::UPDATE);
        $query->addConditionLoginName($loginName);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $this->updateAssignedDepartments($entity);
        return $this->readEntity($loginName, 1);
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
        $query = new Query\UserAccount(Query\Base::DELETE);
        $query->addConditionLoginName($loginName);
        $this->deleteAssignedDepartments($loginName);
        return $this->deleteItem($query);
    }

    protected function updateAssignedDepartments($entity)
    {
        $loginName = $entity->id;
        $this->deleteAssignedDepartments($loginName);
        $query = Query\UserAccount::QUERY_WRITE_ASSIGNED_DEPARTMENTS;
        $statement = $this->getWriter()->prepare($query);
        $userId = $this->readEntityIdByLoginName($loginName);
        foreach ($entity->departments as $department) {
            $statement->execute(
                array (
                    $userId,
                    $department['id']
                )
            );
        }
    }

    protected function readEntityIdByLoginName($loginName)
    {
        $query = Query\UserAccount::QUERY_READ_ID_BY_USERNAME;
        $result = $this->getReader()->fetchOne($query, [$loginName]);
        return $result['id'];
    }

    protected function deleteAssignedDepartments($loginName)
    {
        $query = Query\UserAccount::QUERY_DELETE_ASSIGNED_DEPARTMENTS;
        $statement = $this->getWriter()->prepare($query);
        $userId = $this->readEntityIdByLoginName($loginName);
        $statement->execute([$userId]);
    }
}
