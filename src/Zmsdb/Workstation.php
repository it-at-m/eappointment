<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Workstation as Entity;

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
        $workstation->useraccount = (new UserAccount)->readEntity($loginName, $resolveReferences);
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
                $workstation->authKey = $authKey;
            }
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
