<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\UserAccount as Entity;
use \BO\Zmsentities\Collection\UserAccountList as Collection;

class UserAccount extends Base
{
    public function readIsUserExisting($loginName)
    {
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionLoginName($loginName);
        $userAccount = $this->fetchOne($query, new Entity());
        return ($userAccount->hasId()) ? true : false;
    }

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

    /**
     * read list of useraccounts
     *
     * @param
     * resolveReferences
     *
     * @return Resource Collection
     */
    public function readList($resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\UserAccount(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $entity = $this->readEntity($entity->id, $resolveReferences);
                if ($entity instanceof Entity) {
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
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
        $query = new Query\UserAccount(Query\Base::INSERT);
        if ($this->readIsUserExisting($entity->id) || !$entity->hasProperties('id', 'password', 'rights')) {
            $userAccount = new \BO\Zmsentities\UserAccount();
        } else {
            $values = $query->reverseEntityMapping($entity);
            $query->addValues($values);
            $this->writeItem($query);
            $userAccount = $this->readEntity($entity->id, 1);
        }
        return $userAccount;
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
