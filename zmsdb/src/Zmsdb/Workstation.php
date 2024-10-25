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
                $workstation->scope = (new Scope)->readResolvedReferences(
                    new ScopeEntity($workstation->scope),
                    $resolveReferences - 1
                );
                $workstation->scope->cluster = (new Cluster)->readByScopeId($workstation->scope->id);
                $department = (new Department())->readByScopeId($workstation->scope['id']);
                $workstation->linkList = (new Link())->readByDepartmentId($department->getId());
            }
            $workstation->process = (new Process)->readByWorkstation($workstation, $resolveReferences - 1);
            $config = (new Config)->readEntity();
            $workstation->support = $config->support;
        }
        return $workstation;
    }

    public function readLoggedInHashByName($loginName)
    {
        $query = Query\Workstation::QUERY_LOGGEDIN_CHECK;
        $loggedInWorkstation = $this->getReader()->fetchOne($query, ['loginName' => $loginName]);
        return ($loggedInWorkstation['hash']) ? $loggedInWorkstation['hash'] : null;
    }

    public function readLoggedInListByScope($scopeId, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $workstationList = new \BO\Zmsentities\Collection\WorkstationList();
        $query = new Query\Workstation(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionWorkstationIsNotCounter()
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

    public function writeEntityLoginByOidc($loginName, $authKey, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $workstation = new Entity();
        $query = Query\Workstation::QUERY_LOGIN_OIDC;
        $result = $this->perform(
            $query,
            array(
                $authKey,
                $dateTime->format('Y-m-d'),
                $loginName
            )
        );
        if ($result) {
            $workstation = $this->readEntity($loginName, $resolveReferences);
            $workstation->authkey = $authKey;
            $query = Query\Workstation::QUERY_PROCESS_RESET;
            $this->perform($query, [$workstation->id]);
        }
        return $workstation;
    }

    public function writeEntityLoginByName($loginName, $password, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $useraccount = new Useraccount();
        $workstation = new Entity();
        if ($useraccount->readIsUserExisting($loginName, $password)) {
            $query = Query\Workstation::QUERY_LOGIN;
            $authKey = (new \BO\Zmsentities\Workstation())->getAuthKey();
            $result = $this->perform(
                $query,
                array(
                    $authKey,
                    $dateTime->format('Y-m-d'),
                    $dateTime->format('Y-m-d H:i:s'),
                    $loginName,
                    $password
                )
            );
            if ($result) {
                $workstation = $this->readEntity($loginName, $resolveReferences);
                $workstation->authkey = $authKey;
                $query = Query\Workstation::QUERY_PROCESS_RESET;
                $this->perform($query, [$workstation->id]);
            }
        } else {
            throw new Exception\Useraccount\InvalidCredentials();
        }
        return $workstation;
    }

    public function writeEntityLogoutByName($loginName, $resolveReferences = 0)
    {
        $query = Query\Workstation::QUERY_LOGOUT;
        $result = $this->perform($query, [$loginName]);
        $workstation = $this->readEntity($loginName, $resolveReferences);
        return ($result) ? $workstation : null;
    }

    /**
     *
     * assign a process to workstation
     *
     * @param
     *            workstation
     *
     * @return Resource Process
     */
    public function writeAssignedProcess(
        \BO\Zmsentities\Workstation $workstation,
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $dateTime
    ) {
        $processEntity = $process;
        $process = (new Process)->updateEntity(
            $process,
            $dateTime,
            0,
            null,
            $workstation->getUseraccount()
        );
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process->id);
        $query->addValues(['NutzerID' => $workstation->id]);
        $this->writeItem($query);
        $checksum = sha1($process->id . '-' . $workstation->getUseraccount()->id);
        Log::writeProcessLog(
            "UPDATE (Workstation::writeAssignedProcess) $checksum ",
            Log::ACTION_CALLED,
            $processEntity
        );

        return $process;
    }

    /**
     *
     * remove a process from workstation
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
                'nicht_erschienen' => ('missed' == $process->status) ? 1 : 0,
                'parked' => ('parked' == $process->status) ? 1 : 0
            ]
        );
        Log::writeProcessLog(
            "UPDATE (Workstation::writeRemovedProcess)",
            Log::ACTION_REMOVED,
            $process,
            $workstation->getUseraccount()
        );
        return $this->writeItem($query);
    }


    /**
     * update a workstation
     *
     * @param
     * useraccountId
     *
     * @return Entity
     */
    public function updateEntity(\BO\Zmsentities\Workstation $entity, $resolveReferences = 0)
    {
        $query = new Query\Workstation(Query\Base::UPDATE);
        $query->addConditionWorkstationId($entity->getId());
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);

        if ($this->perform($query->getLockWorkstationId(), ['workstationId' => $entity->getId()])) {
            $this->writeItem($query);
        }
        return $this->readEntity($entity->useraccount['id'], $resolveReferences);
    }

    /**
     * update a workstations authkey - is needed for openid login
     *
     * @param
     * useraccountId
     *
     * @return Entity
     */
    public function updateEntityAuthkey($loginName, $password, $authKey, $resolveReferences)
    {
        $query = Query\Workstation::QUERY_UPDATE_AUTHKEY;
        $result = $this->perform(
            $query,
            array(
                $authKey,
                $loginName,
                $password
            )
        );
        if ($result) {
            $workstation = $this->readEntity($loginName, $resolveReferences);
            $workstation->authkey = $authKey;
        }
        return $workstation;
    }
}
