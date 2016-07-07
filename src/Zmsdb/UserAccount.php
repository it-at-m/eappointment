<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\UserAccount as Entity;

class UserAccount extends Base
{
    public function readEntity($loginname, $resolveReferences = 0)
    {
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionLoginName($loginname);
        $userAccount = $this->fetchOne($query, new Entity());
        return $userAccount;
    }

    public function readEntityByAuthKey($xAuthKey, $resolveReferences = 0)
    {
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionXauthKey($xAuthKey);
        return ($xAuthKey) ? $this->fetchOne($query, new Entity()) : new Entity();
    }

    /**
     * write an userAccount
     *
     * @param
     * entity
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\UserAccount $entity)
    {
        if (count($this->readEntity($entity->id))) {
            return null;
        }
        $query = new Query\UserAccount(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($entity->id, 1);
    }


    /**
     * update a userAccount
     *
     * @param
     * userAccountId
     *
     * @return Entity
     */
    public function updateEntity($loginName, \BO\Zmsentities\UserAccount $entity)
    {
        $query = new Query\UserAccount(Query\Base::UPDATE);
        $query->addConditionLoginName($loginName);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($loginName, 1);
    }

    /**
     * remove an user
     *
     * @param
     * itemId
     *
     * @return Resource Status
     */
    public function deleteEntity($loginName)
    {
        $query =  new Query\UserAccount(Query\Base::DELETE);
        $query->addConditionLoginName($loginName);
        return $this->deleteItem($query);
    }
}
