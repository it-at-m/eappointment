<?php

namespace BO\Zmsbackend\Process\Service;

use BO\Zmsentities\Process as Entity;
use BO\Zmsentities\Collection\ProcessList as Collection;

/**
 *
 */
class ProcessStatusQueued extends Process
{
    public function writeNewFromTicketprinter(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $dateTime,
        array $requestIds = []
    ) {
        $process = Entity::createFromScope($scope, $dateTime);
        $process->setStatus('queued');
        $process->isTicketprinter = true;
        $newQueueNumber = (new \BO\Zmsbackend\Scope\Service\Scope())->readWaitingNumberUpdated($scope->id, $dateTime);
        $process->addQueue($newQueueNumber, $dateTime);
        $process = $this->writeNewProcess($process, $dateTime);

        $process->updateRequests($scope->getSource(), implode(',', $requestIds));
        $this->writeRequestsToDb($process);

        return $process;
    }

    public function writeNewFromAdmin(Entity $process, \DateTimeInterface $dateTime)
    {
        $process->setStatus('queued');
        $process->getFirstAppointment()->date = $dateTime->modify('00:00:00')->getTimestamp();
        $newQueueNumber = (new \BO\Zmsbackend\Scope\Service\Scope())->readWaitingNumberUpdated($process->scope['id'], $dateTime, false);
        $process->addQueue($newQueueNumber, $dateTime);
        $process = $this->writeNewProcess($process, $dateTime);
        if (0 < $process->getRequests()->count()) {
            $this->writeRequestsToDb($process);
        }
        return $this->readEntity($process->id, $process->authKey, 2);
    }

    /**
     * Read process by queue number and scopeId
     *
     * @param
     * scopeId
     *
     * @return string authKey
     */
    public function readByQueueNumberAndScope($queueNumber, $scopeId, $resolveReferences = 0, $queueLimit = 10000)
    {
        $query = new \BO\Zmsbackend\Process\Repository\Process(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId)
            ->addConditionAssigned()
            ->addConditionQueueNumber($queueNumber, $queueLimit);
        $process = $this->fetchOne($query, new Entity());
        $process = $this->readResolvedReferences($process, $resolveReferences);
        return $process;
    }
}
