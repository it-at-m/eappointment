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

    public function readIsUserExisting($loginName, $password)
    {
        $query = new Query\Workstation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionLoginName($loginName)
            ->addConditionPassword($password);
        $workstation = $this->fetchOne($query, new Entity());
        return ($workstation->hasId()) ? true : false;
    }

    public function readUpdatedLoginEntity($loginName, $password)
    {
        $workstation = new Entity();
        if ($this->readIsUserExisting($loginName, $password)) {
            $query = Query\Workstation::QUERY_LOGIN;
            $statement = $this->getWriter()->prepare($query);
            $authKey = (new \BO\Zmsentities\Workstation())->getAuthKey();
            $result = $statement->execute(
                array(
                    $authKey,
                    (new \DateTimeImmutable())->format('Y-m-d'),
                    $loginName
                )
            );
            if ($result) {
                $workstation = $this->readEntity($loginName);
                $workstation->authKey = $authKey;
            }
        }
        return $workstation;
    }

    public function readUpdatedLogoutEntity($loginName)
    {
        $query = Query\Workstation::QUERY_LOGOUT;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
                $loginName
            )
        );
        $workstation = $this->readEntity($loginName);
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
    public function updateEntity(\BO\Zmsentities\Workstation $entity)
    {
        $query = new Query\Workstation(Query\Base::UPDATE);
        $query->addConditionWorkstationId($entity->id);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($entity->useraccount['id']);
    }
}
