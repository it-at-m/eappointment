<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Workstation as Entity;
use \BO\Zmsentities\Useraccount as UseraccountEntity;
use \BO\Zmsentities\Scope as ScopeEntity;
use \BO\Zmsentities\Process as ProcessEntity;

use \BO\Zmsentities\Collection\WorkstationList as Collection;

/**
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Public)
 *
 */
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

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $workstation, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            $workstation->useraccount = (new Useraccount())
                ->readResolvedReferences(
                    new UseraccountEntity($workstation->useraccount),
                    $resolveReferences - 1
                );
            if ($workstation->scope['id']) {
                $scopeData = (new Query\Scope(Query\Base::SELECT))->postProcess($workstation->scope);
                $workstation->scope = (new Scope)->readResolvedReferences(
                    new ScopeEntity($scopeData),
                    $resolveReferences - 1
                );
                $workstation->linkList = (new Link)->readByScopeId($workstation->scope['id']);
            }
            $workstation->process = (new Process)->readByWorkstation(
                $workstation,
                $resolveReferences - 1
            );
        }
        return $workstation;
    }

    public function readLoggedInHashByName($loginName)
    {
        $query = Query\Workstation::getQueryLoggedInCheck();
        $LoggedInWorkstation = $this->getReader()->fetchOne(
            $query,
            ['loginName' => $loginName]
        );
        return ($LoggedInWorkstation['hash']) ? $LoggedInWorkstation['hash'] : null;
    }

    public function readLoggedInListByScope($scopeId, \DateTimeInterface $dateTime, $resolveReferences = 0)
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
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $workstationList->addEntity($entity);
                }
            }
        }
        return $workstationList;
    }

    public function readLoggedInListByCluster($clusterId, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $workstationList = new \BO\Zmsentities\Collection\WorkstationList();
        $cluster = (new Cluster)->readEntity($clusterId, $resolveReferences);
        if ($cluster->toProperty()->scopes->get()) {
            foreach ($cluster->scopes as $scope) {
                $workstationList->addList($this->readLoggedInListByScope($scope['id'], $dateTime, $resolveReferences));
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
        $workstation = $this->readResolvedReferences($workstation, $resolveReferences);
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
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    public function writeEntityLoginByName($loginName, $password, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $userAccount = new Useraccount();
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
     *
     * Weise den Process einer Workstation zu
     *
     * @param
     *            workstation
     *
     * @return Resource Process
     */
    public function writeAssignedProcess($workstationId, \BO\Zmsentities\Process $process)
    {
        $process = (new Process)->updateEntity($process);
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process->id);
        $query->addValues(['NutzerID' => $workstationId]);
        $this->writeItem($query);
        return $process;
    }

    /**
     *
     * entferne den Process aus einer Workstation
     *
     * @param
     *            workstation
     *
     * @return Boolean
     */
    public function writeRemovedProcess(\BO\Zmsentities\Workstation $workstation)
    {
        $process = new \BO\Zmsentities\Process($workstation->process);
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process->id);
        $query->addValues(
            [
                'aufrufzeit' => 0,
                'NutzerID' => 0,
                'AnzahlAufrufe' => $process->queue['callCount'],
                'nicht_erschienen' => ('missed' == $process->status) ? 1 : 0
            ]
        );
        return $this->writeItem($query);
        //Log::writeLogEntry("UPDATE (Process::writeRemovedWorkstation $workstation->id) $process ", $process->id);
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
