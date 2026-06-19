<?php

namespace BO\Zmsbackend\Workstation\Service;

use BO\Zmsentities\Workstation as Entity;
use BO\Zmsentities\Useraccount as UseraccountEntity;
use BO\Zmsentities\Scope as ScopeEntity;
use BO\Zmsentities\Process as ProcessEntity;
use BO\Zmsentities\Collection\WorkstationList as Collection;

/**
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Public)
 *
 */
class Workstation extends \BO\Zmsbackend\Base
{
    public function readEntity($loginName, $resolveReferences = 0)
    {
        $query = new \BO\Zmsbackend\Workstation\Repository\Workstation(\BO\Zmsbackend\Query\Base::SELECT);
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

    #[\Override]
    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            $entity->useraccount = (new \BO\Zmsbackend\Useraccount\Service\Useraccount())
                ->readResolvedReferences(
                    new UseraccountEntity($entity->useraccount),
                    $resolveReferences - 1
                );
            if ($entity->scope['id']) {
                $entity->scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readResolvedReferences(
                    new ScopeEntity($entity->scope),
                    $resolveReferences - 1
                );
                $entity->scope->cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readByScopeId($entity->scope->id);
                $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($entity->scope['id']);
                $entity->linkList = (new \BO\Zmsbackend\Link\Service\Link())->readByDepartmentId($department->getId());
            }
            $entity->process = (new \BO\Zmsbackend\Process\Service\Process())->readByWorkstation($entity, $resolveReferences - 1);
            $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
            $entity->support = $config->support;
        }
        return $entity;
    }

    public function readLoggedInHashByName($loginName)
    {
        $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_LOGGEDIN_CHECK;
        $loggedInWorkstation = $this->getReader()->fetchOne($query, ['loginName' => $loginName]);
        return ($loggedInWorkstation['hash']) ? $loggedInWorkstation['hash'] : null;
    }

    public function readLoggedInListByScope($scopeId, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $workstationList = new \BO\Zmsentities\Collection\WorkstationList();
        $query = new \BO\Zmsbackend\Workstation\Repository\Workstation(\BO\Zmsbackend\Query\Base::SELECT);
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
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($clusterId, $resolveReferences);
        if ($cluster->toProperty()->scopes->get()) {
            foreach ($cluster->scopes as $scope) {
                $workstationList->addList($this->readLoggedInListByScope($scope['id'], $dateTime, $resolveReferences));
            }
        }
        return $workstationList;
    }

    public function readWorkstationByScopeAndName($scopeId, $workstationName, $resolveReferences = 0)
    {
        $query = new \BO\Zmsbackend\Workstation\Repository\Workstation(\BO\Zmsbackend\Query\Base::SELECT);
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
        $query = new \BO\Zmsbackend\Workstation\Repository\Workstation(\BO\Zmsbackend\Query\Base::SELECT);
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

    public function writeEntityLoginByOidc($loginName, $authKey, \DateTimeInterface $dateTime, \DateTimeInterface $sessionExpiry, $resolveReferences = 0)
    {
        $workstation = new Entity();
        $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_LOGIN_OIDC;
        $result = $this->perform(
            $query,
            array(
                $authKey,
                $sessionExpiry->format('Y-m-d H:i:s'),
                $dateTime->format('Y-m-d'),
                $loginName
            )
        );
        if ($result) {
            $workstation = $this->readEntity($loginName, $resolveReferences);
            $workstation->authkey = $authKey;
            $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_PROCESS_RESET;
            $this->perform($query, [$workstation->id]);
        }
        return $workstation;
    }

    public function writeEntityLoginByName($loginName, $password, \DateTimeInterface $dateTime, \DateTimeInterface $sessionExpiry, $resolveReferences = 0)
    {
        $useraccount = new \BO\Zmsbackend\Useraccount\Service\Useraccount();
        $workstation = new Entity();
        if ($useraccount->readIsUserExisting($loginName, $password)) {
            $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_LOGIN;
            $authKey = (new \BO\Zmsentities\Workstation())->getAuthKey();
            $result = $this->perform(
                $query,
                array(
                    $authKey,
                    $sessionExpiry->format('Y-m-d H:i:s'),
                    $dateTime->format('Y-m-d'),
                    $dateTime->format('Y-m-d H:i:s'),
                    $loginName,
                    $password
                )
            );
            if ($result) {
                $workstation = $this->readEntity($loginName, $resolveReferences);
                $workstation->authkey = $authKey;
                $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_PROCESS_RESET;
                $this->perform($query, [$workstation->id]);
            }
        } else {
            throw new \BO\Zmsbackend\Useraccount\Exception\InvalidCredentials();
        }
        return $workstation;
    }

    public function writeEntityLogoutByName($loginName, $resolveReferences = 0)
    {
        $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_LOGOUT;
        $result = $this->perform($query, [$loginName]);
        $workstation = $this->readEntity($loginName, $resolveReferences);
        return ($result) ? $workstation : null;
    }

    /**
     *
     * assign a process to workstation
     *
     * @param \BO\Zmsentities\Workstation $workstation
     * @param \BO\Zmsentities\Process $process
     *
     * @return ProcessEntity
     */
    public function writeAssignedProcess(
        \BO\Zmsentities\Workstation $workstation,
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $dateTime
    ) {
        $processEntity = $process;
        $processDb = new \BO\Zmsbackend\Process\Service\Process();

        $assignedWorkstationId = $processDb->readAssignedWorkstationIdForUpdate((int) $process->getId());

        if (
            $assignedWorkstationId !== null
            && $assignedWorkstationId !== 0
            && $assignedWorkstationId !== (int) $workstation->getId()
        ) {
            throw new \DomainException('PROCESS_ALREADY_ASSIGNED');
        }
        $process = (new \BO\Zmsbackend\Process\Service\Process())->updateEntity(
            $process,
            $dateTime,
            0,
            null,
            $workstation->getUseraccount()
        );
        $query = new \BO\Zmsbackend\Process\Repository\Process(\BO\Zmsbackend\Query\Base::UPDATE);
        $query->addConditionProcessId($process->id);
        $query->addValues(['NutzerID' => $workstation->id]);
        $this->writeItem($query);
        $checksum = sha1($process->id . '-' . $workstation->getUseraccount()->id);
        \BO\Zmsbackend\Log\Service\Log::writeProcessLog(
            "UPDATE (\BO\Zmsbackend\Workstation\Service\Workstation::writeAssignedProcess) $checksum ",
            \BO\Zmsbackend\Log\Service\Log::ACTION_CALLED,
            $processEntity
        );

        return $process;
    }

    /**
     *
     * remove a process from workstation
     *
     * @param \BO\Zmsentities\Workstation $workstation
     *
     * @return bool
     */
    public function writeRemovedProcess(\BO\Zmsentities\Workstation $workstation)
    {
        $process = new \BO\Zmsentities\Process($workstation->process);
        $query = new \BO\Zmsbackend\Process\Repository\Process(\BO\Zmsbackend\Query\Base::UPDATE);
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
        \BO\Zmsbackend\Log\Service\Log::writeProcessLog(
            "UPDATE (\BO\Zmsbackend\Workstation\Service\Workstation::writeRemovedProcess)",
            \BO\Zmsbackend\Log\Service\Log::ACTION_REMOVED,
            $process,
            $workstation->getUseraccount()
        );
        return $this->writeItem($query);
    }


    /**
     * update a workstation
     *
     * @param int|string $useraccountId
     *
     * @return Entity
     */
    public function updateEntity(\BO\Zmsentities\Workstation $entity, $resolveReferences = 0)
    {
        $query = new \BO\Zmsbackend\Workstation\Repository\Workstation(\BO\Zmsbackend\Query\Base::UPDATE);
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
     * @param int|string $useraccountId
     *
     * @return Entity
     */
    public function updateEntityAuthkey($loginName, $password, $authKey, \DateTimeInterface $sessionExpiry, $resolveReferences)
    {
        $query = \BO\Zmsbackend\Workstation\Repository\Workstation::QUERY_UPDATE_AUTHKEY;
        $result = $this->perform(
            $query,
            array(
                $authKey,
                $sessionExpiry->format('Y-m-d H:i:s'),
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
