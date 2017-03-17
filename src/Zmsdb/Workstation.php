<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Workstation as Entity;
use \BO\Zmsentities\Useraccount as UseraccountEntity;
use \BO\Zmsentities\Scope as ScopeEntity;

use \BO\Zmsentities\Collection\WorkstationList as Collection;

class Workstation extends Base
{
    public function readEntity($loginName, $resolveReferences = 0)
    {
        $query = new Query\Workstation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionLoginName($loginName)
            ->addResolvedReferences($resolveReferences);
        $workstation = $this->fetchOne($query, new Entity());
        if (! $workstation->hasId()) {
            return null;
        }
        return $this->readResolvedReferences($workstation, $resolveReferences);
    }

    public function readResolvedReferences(\BO\Zmsentities\Workstation $workstation, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            $workstation->useraccount = (new UserAccount())
                ->readResolvedReferences(
                    new UseraccountEntity($workstation->useraccount),
                    $resolveReferences - 1
                );
            if ($workstation->scope['id']) {
                $workstation->scope = (new Scope)->readEntity($workstation->scope['id'], $resolveReferences - 1);
                $department = (new Department)->readByScopeId($workstation->scope['id']);
                $workstation->linkList = $department->links;
            }
        }
        return $workstation;
    }

    public function readByScopeAndDay($scopeId, $dateTime, $resolveReferences = 0)
    {
        $workstationList = new \BO\Zmsentities\Collection\WorkstationList();
        $query = new Query\Workstation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionTime($dateTime)
            ->addResolvedReferences($resolveReferences);

        $result = $this->fetchList($query, new Entity());

        if ($result) {
            foreach ($result as $entity) {
                if ($entity->hasId()) {
                    $entity->useraccount = (new UserAccount)->readEntity($entity->id, $resolveReferences);
                    $workstationList->addEntity($entity);
                }
            }
        }
        return $workstationList;
    }

    public function readWorkstationByScopeAndName($scopeId, $workstationName, $resolveReferences = 0)
    {
        $query = new Query\Workstation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionWorkstationName($workstationName)
            ->addResolvedReferences($resolveReferences);
        $workstation = $this->fetchOne($query, new Entity());
        if (! $workstation->hasId()) {
            return null;
        }
        $workstation->useraccount = (new UserAccount)->readEntityByUserId($workstation->id, $resolveReferences - 1);
        return $workstation;
    }

    public function readCollectionByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Workstation(Query\Base::SELECT);
        $query->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId)
            ->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity->useraccount = (new UserAccount)->readEntityByUserId($entity->id, $resolveReferences - 1);
                    $entity->scope = (new Scope)->readEntity($entity->scope['id'], $resolveReferences - 1);
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    public function writeEntityLoginByName($loginName, $password, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $userAccount = new UserAccount();
        $workstation = new Entity();
        if ($userAccount->readIsUserExisting($loginName, $password)) {
            $query = Query\Workstation::QUERY_LOGIN;
            $statement = $this->getWriter()->prepare($query);
            $authKey = (new \BO\Zmsentities\Workstation())->getAuthKey();
            $result = $statement->execute(
                array(
                    $authKey,
                    $dateTime->format('Y-m-d'),
                    $loginName,
                    md5($password)
                )
            );
            if ($result) {
                $workstation = $this->readEntity($loginName, $resolveReferences);
                $workstation->authkey = $authKey;
            }
        } else {
            throw new Exception\Useraccount\InvalidCredentials();
        }
        return $workstation;
    }

    public function writeEntityLogoutByName($loginName, $resolveReferences = 0)
    {
        $query = Query\Workstation::QUERY_LOGOUT;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
                $loginName
            )
        );
        $workstation = $this->readEntity($loginName, $resolveReferences);
        return ($result) ? $workstation : null;
    }

    /**
     * update a workstation
     *
     * @param
     * userAccountId
     *
     * @return Entity
     */
    public function updateEntity(\BO\Zmsentities\Workstation $entity, $resolveReferences = 0)
    {
        $query = new Query\Workstation(Query\Base::UPDATE);
        $query->addConditionWorkstationId($entity->id);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($entity->useraccount['id'], $resolveReferences);
    }
}
