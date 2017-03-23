<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Collection\ProcessList as Collection;
use BO\Zmsdb\Helper\ProcessStatus as Status;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(TooManyPublicMethods)
 */
class Process extends Base
{
    public function readEntity($processId = null, $authKey = null, $resolveReferences = 2)
    {
        if (null === $processId || null === $authKey) {
            return null;
        }
        $query = new Query\Process(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessId($processId)
            ->addConditionAuthKey($authKey);
        $process = $this->fetchOne($query, new Entity());
        if ($process->id != $processId) {
            throw new Exception\ProcessAuthFailed("Could not find process $processId identified by '$authKey'");
        }
        $process = $this->readResolvedReferences($process, $resolveReferences);
        return $process;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $process, $resolveReferences)
    {
        if (1 <= $resolveReferences) {
            $process['requests'] = (new Request())->readRequestByProcessId($process->id, $resolveReferences - 1);
        }
        return $process;
    }

    public function updateEntity(\BO\Zmsentities\Process $process)
    {
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process['id']);
        $query->addConditionAuthKey($process['authKey']);
        $query->addValuesUpdateProcess($process);
        $this->writeItem($query);
        $this->writeRequestsToDb($process);

        $process = $this->readEntity($process->id, $process->authKey);

        Log::writeLogEntry("UPDATE (Process::updateEntity) $process ", $process->id);
        return $process;
    }

    /**
     * Insert a new process if there are free slots
     *
     * @SuppressWarnings("unused")
     */
    public function writeEntityReserved(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        $slotType = "public"
    ) {
        $process->status = 'reserved';
        $appointment = $process->getAppointments()->getFirst();
        $freeProcessList = $this->readFreeProcesses($process->toCalendar(), $now);
        if (!$freeProcessList->getAppointmentList()->hasAppointment($appointment)) {
            throw new Exception\ProcessReserveFailed();
        }
        $slotList = (new Slot)->readByAppointment($appointment);
        /*
        if (!$slotList->isAvailableForAll($slotType)) {
            throw new Exception\ProcessReserveFailed("Could not reserve multiple slots");
        }
        */
        foreach ($slotList as $slot) {
            if ($process->id > 99999) {
                $newProcess = clone $process;
                $newProcess->getFirstAppointment()->setTime($slot->time);
                $this->writeNewProcess($newProcess, $now, $process->id);
            } elseif ($process->id === 0) {
                $process = $this->writeNewProcess($process, $now, 0, count($slotList) - 1);
            } else {
                throw new \Exception("SQL UPDATE error on inserting new $process on $slot");
            }
        }
        $this->writeRequestsToDb($process);
        return $process;
    }

    public function writeNewFromTicketprinter($scope, \DateTimeInterface $dateTime)
    {
        $appointment = new \BO\Zmsentities\Appointment();
        $appointment->addScope($scope->id);
        $appointment->addSlotCount(0);
        $appointment->addDate($dateTime->modify('00:00:00')->getTimestamp());
        $process = new Entity();
        $process->scope = $scope;
        $process->setStatus('queued');
        $process->addAppointment($appointment);
        $newQueueNumber = (new Scope())->readWaitingNumberUpdated($scope->id, $dateTime);
        $process->addQueue($newQueueNumber, $dateTime);
        return $this->writeNewProcess($process, $dateTime);
    }

    public function readSlotCount(\BO\Zmsentities\Process $process)
    {
        $scope = new \BO\Zmsentities\Scope($process->scope);
        $slotCountList = (new Provider())->readSlotCountById($scope->getProviderId());
        $appointment = $process->getAppointments()->getFirst();
        $appointment->slotCount = 0;
        foreach ($process->requests as $request) {
            foreach ($slotCountList as $slotCount) {
                if ($slotCount['request__id'] == $request['id']) {
                    $appointment->slotCount += $slotCount['slots'];
                }
            }
        }
        return $process;
    }

    /**
     * write a new process to DB
     *
     */
    protected function writeNewProcess(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        $parentProcess = 0,
        $childProcessCount = 0
    ) {
        $query = new Query\Process(Query\Base::INSERT);
        $process->id = $this->readNewProcessId();
        $process->setRandomAuthKey();
        $process->createTimestamp = $now->getTimestamp();
        $query->addValuesNewProcess($process, $parentProcess, $childProcessCount);
        $query->addValuesUpdateProcess($process);
        $this->writeItem($query);
        Log::writeLogEntry("CREATE (Process::writeNewProcess) $process ", $process->id);
        return $process;
    }

    /**
     * Fetch a free process ID from DB
     *
     */
    protected function readNewProcessId()
    {
        $query = new Query\Process(Query\Base::SELECT);
        $newProcessId = 100000;
        if ($this->getReader()->fetchValue($query->getFirstSixDigitProcessId())) {
            $newProcessId = $this->getReader()->fetchValue($query->getQueryNewProcessId());
        }
        return $newProcessId;
    }

    /**
     * Read authKey by processId
     *
     * @param
     * processId
     *
     * @return String authKey
     */
    public function readAuthKeyByProcessId($processId)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query->addEntityMapping()->addConditionProcessId($processId);
        $process = $this->fetchOne($query, new Entity());
        return ($process->hasId()) ? array(
            'authName' => $process->getFirstClient()['familyName'],
            'authKey' => $process->authKey
        ) : null;
    }

    /**
     * Read process by queue number and scopeId
     *
     * @param
     * scopeId
     *
     * @return String authKey
     */
    public function readByQueueNumberAndScope($queueNumber, $scopeId)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionAssigned()
            ->addConditionQueueNumber($queueNumber);
        $process = $this->fetchOne($query, new Entity());
        return $process;
    }

    protected function readList($statement, $resolveReferences)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $processList = new Collection();
        while ($processData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcess($processData));
            $entity = $this->readResolvedReferences($entity, $resolveReferences);
            $processList->addEntity($entity);
        }
        return $processList;
    }

    /**
     * Read processList by scopeId and DateTime
     *
     * @param
     * scopeId
     * dateTime
     *
     * @return Collection processList
     */
    public function readProcessListByScopeAndTime($scopeId, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionAssigned()
            ->addConditionTime($dateTime);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    public function readSearch($queryString, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionSearch($queryString)
            ->addConditionAssigned()
            ;
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    /**
     * Read processList by clusterId and DateTime
     *
     * @param
     * clusterId
     * dateTime
     *
     * @return Collection processList
     */
    public function readProcessListByClusterAndTime($clusterId, \DateTimeInterface $dateTime)
    {
        $processList = new Collection();
        $cluster = (new Cluster)->readEntity($clusterId, 1);
        if ($cluster->scopes->count()) {
            foreach ($cluster->scopes as $scope) {
                $processList->addList($this->readProcessListByScopeAndTime($scope->id, $dateTime));
            }
        }
        return $processList;
    }

    /**
     * Read processList by scopeId to get a number of all processes of a scope
     *
     * @param
     * scopeId
     *
     * @return Collection processList
     */
    public function readProcessListCountByScope($scopeId)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addCountValue()
            ->addConditionAssigned()
            ->addConditionScopeId($scopeId);
        $statement = $this->fetchStatement($query);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result['processCount'];
    }

    /**
     * Markiere einen Termin als bestätigt
     *
     * @param
     * process
     *
     * @return Resource Status
     */
    public function updateProcessStatus(\BO\Zmsentities\Process $process, $status = 'free')
    {
        //\App::$log->debug('UPDATE STATUS');
        $process = (new Status())->readUpdatedStatus($process, $status);
        return $process;
    }

    /**
     * Markiere einen Termin als abgesagt
     *
     * @param
     *            processId and authKey
     *
     * @return Resource Status
     */
    public function deleteEntity($processId, $authKey)
    {
        $query = Query\Process::QUERY_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $status = $statement->execute(
            array(
                $processId,
                $authKey,
                $processId
            )
        );
        if ($status) {
            $query =  new Query\XRequest(Query\Base::DELETE);
            $query->addConditionProcessId($processId);
            $status = $this->deleteItem($query);
        }
        Log::writeLogEntry("DELETE (Process::deleteEntity) $processId ", $processId);
        return $status;
    }

    protected function writeRequestsToDb(\BO\Zmsentities\Process $process)
    {
        $deleteQuery = new Query\XRequest(Query\Base::DELETE);
        $deleteQuery->addConditionProcessId($process->id);
        $this->deleteItem($deleteQuery);
        $query = new Query\XRequest(Query\Base::INSERT);
        foreach ($process->requests as $request) {
            $query->addValues(
                [
                    'AnliegenID' => $request['id'],
                    'BuergerID' => $process->id
                ]
            );
            $this->writeItem($query);
        }
    }

    

    /**
     * Todo: Refactoring, writeRemovedWorkstation zu writeRemovedProcess in Workstation Klasse
     *
     * entferne den Process aus einer Workstation
     *
     * @param
     *            workstation
     *
     * @return Boolean
     */
    public function writeRemovedWorkstation(\BO\Zmsentities\Workstation $workstation)
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

    public function readFreeProcesses(\BO\Zmsentities\Calendar $calendar, \DateTimeInterface $now)
    {
        $resolvedCalendar = new Calendar();
        $selectedDate = $calendar->getFirstDay();
        $calendar->setLastDayTime($selectedDate);
        $calendar = $resolvedCalendar->readResolvedEntity($calendar, $now, $selectedDate);
        return (isset($calendar['freeProcesses'])) ? $calendar['freeProcesses'] : new Collection();
    }

    public function readReservedProcesses($resolveReferences = 2)
    {
        $processList = new Collection();
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionAssigned()
            ->addConditionIsReserved();
        $resultData = $this->fetchList($query, new Entity());
        foreach ($resultData as $process) {
            if (2 == $resolveReferences) {
                $process['requests'] = (new Request())->readRequestByProcessId($process->id, $resolveReferences);
                $process['scope'] = (new Scope())->readEntity($process->getScopeId(), $resolveReferences);
            }
            if ($process instanceof Entity) {
                $processList->addEntity($process);
            }
        }
        return $processList;
    }
}
