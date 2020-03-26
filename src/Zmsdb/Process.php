<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Collection\ProcessList as Collection;
use BO\Zmsdb\Helper\ProcessStatus;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(TooManyMethods)
 */
class Process extends Base implements Interfaces\ResolveReferences
{
    public function readEntity($processId = null, $authKey = null, $resolveReferences = 2)
    {
        if (null === $processId || null === $authKey) {
            return null;
        }
        $query = new Query\Process(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessId($processId);
        if (!$authKey instanceof Helper\NoAuth) {
            $query->addConditionAuthKey($authKey);
        }
        $process = $this->fetchOne($query, new Entity());
        $process = $this->readResolvedReferences($process, $resolveReferences);
        return $process;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $process, $resolveReferences)
    {
        if (1 <= $resolveReferences) {
            if ($process->archiveId) {
                $process['requests'] = (new Request())
                    ->readRequestByArchiveId($process->archiveId, $resolveReferences - 1);
            } else {
                $process['requests'] = (new Request())
                    ->readRequestByProcessId($process->id, $resolveReferences - 1);
            }
        }
        return $process;
    }

    public function updateEntity(\BO\Zmsentities\Process $process, \DateTimeInterface $now, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process['id']);
        $query->addConditionAuthKey($process['authKey']);
        $query->addValuesUpdateProcess($process, $now);
        
        if ($this->perform($query->getLockProcessId(), ['processId' => $process->getId()])) {
            $this->writeItem($query);
            $this->writeRequestsToDb($process);
        }
        $process = $this->readEntity($process->getId(), $process->authKey, $resolveReferences);
        if (!$process->getId()) {
            throw new Exception\Process\ProcessUpdateFailed();
        }
        $this->perform(Query\Process::QUERY_UPDATE_FOLLOWING_PROCESS, [
            'reserved' => ($process->status == 'reserved') ? 1 : 0,
            'processID' => $process->getId(),
        ]);
        (new Slot())->deleteSlotProcessMappingFor($process->id);
        (new Slot())->writeSlotProcessMappingFor($process->id);
        Log::writeLogEntry("UPDATE (Process::updateEntity) $process ", $process->getId());
        return $process;
    }

    public function writeNewPickup(\BO\Zmsentities\Scope $scope, \DateTimeInterface $dateTime, $newQueueNumber = 0)
    {
        $process = Entity::createFromScope($scope, $dateTime);
        $process->setStatus('pending');
        if (!$newQueueNumber) {
            $newQueueNumber = (new Scope())->readWaitingNumberUpdated($scope->id, $dateTime);
        }
        $process->addQueue($newQueueNumber, $dateTime);
        Log::writeLogEntry("CREATE (Process::writeNewPickup) $process ", $process->id);
        return $this->writeNewProcess($process, $dateTime);
    }

    public function readSlotCount(\BO\Zmsentities\Process $process)
    {
        $scope = new \BO\Zmsentities\Scope($process->scope);
        $requestRelationList = (new RequestRelation())
            ->readListByProviderId($scope->getProviderId(), $scope->getSource());
        $appointment = $process->getAppointments()->getFirst();
        $appointment->slotCount = 0;
        foreach ($process->requests as $request) {
            foreach ($requestRelationList as $requestRelation) {
                if ($requestRelation->getRequest()->getId() == $request->getId()) {
                    $appointment->slotCount += $requestRelation->getSlotCount();
                }
            }
        }
        $appointment->slotCount = round($appointment->slotCount, 0);
        return $process;
    }

    /**
     * write a new process with appointment and keep id and authkey from original process
     */
    public function writeEntityWithNewAppointment(
        \BO\Zmsentities\Process $process,
        \BO\Zmsentities\Appointment $appointment,
        \DateTimeInterface $now,
        $slotType = 'public',
        $slotsRequired = 0,
        $resolveReferences = 0
    ) {
        // clone to new process with id = 0 and new appointment to reserve
        $processNew = clone $process;
        $processNew->id = 0;
        $processNew->queue['arrivalTime'] = 0;
        $processNew->appointments = (new \BO\Zmsentities\Collection\AppointmentList())->addEntity($appointment);
        $processNew = ProcessStatusFree::init()
            ->writeEntityReserved($processNew, $now, $slotType, $slotsRequired);
        $processTempNewId = $processNew->getId();

        //save old process in temp process and delete old process with following processes
        $this->writeDeletedEntity($process->getId());

        //reserve a new process with temp process data to block process slots
        $processTemp = new \BO\Zmsentities\Process($process);
        try {
            $processTemp->id = 0;
            $processTemp->queue['arrivalTime'] = 0;
            $processTemp = ProcessStatusFree::init()->writeEntityReserved(
                $processTemp,
                $now,
                $slotType,
                $processTemp->getFirstAppointment()->getSlotCount()
            );
        } catch (\Exception $exception) {
            // ignore if not bookable
        }

        // reassign credentials of new process with credentials of old process
        $processNew->withReassignedCredentials($process);
        $processNew->setStatus('confirmed');

        // update new process with new credentials and status, also following slots with ids from old process
        $processNew = $this
            ->updateWithFollowingProcesses($processTempNewId, $processNew, $now, $resolveReferences);
        
        //delete slot mapping for new process id
        (new Slot())->deleteSlotProcessMappingFor($processTempNewId);
        Log::writeLogEntry("UPDATE (Process::writeEntityWithNewAppointment) $process ", $processNew->getId());
        return $processNew;
    }

    /**
     * update following process with new credentials (also change process id if necessary)
     */
    public function updateWithFollowingProcesses(
        $processId,
        \BO\Zmsentities\Process $processData,
        \DateTimeInterface $now,
        $resolveReferences = 0
    ) {
        $this->perform(Query\Process::QUERY_REASSIGN_PROCESS_CREDENTIALS, [
            'newProcessId' => $processData->getId(),
            'newAuthKey' => $processData->getAuthKey(),
            'processId' => $processId
        ]);

        $processEntityList = $this->readEntityList($processId);
        if ($processEntityList->count()) {
            foreach ($processEntityList as $entity) {
                if ($entity->getId() != $processId) {
                    $this->perform(Query\Process::QUERY_REASSIGN_FOLLWING_PROCESS, [
                        'newProcessId' => $processData->getId(),
                        'processId' => $processId
                    ]);
                }
            }
        }
        return $this->updateEntity($processData, $now, $resolveReferences);
    }

    /**
     * write a new process to DB
     *
     */
    protected function writeNewProcess(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $dateTime,
        $parentProcess = 0,
        $childProcessCount = 0,
        $retry = true,
        $userAccount = null
    ) {
        $query = new Query\Process(Query\Base::INSERT);
        $process->id = $this->readNewProcessId();
        $process->setRandomAuthKey();
        $process->createTimestamp = $dateTime->getTimestamp();
        $query->addValuesNewProcess($process, $parentProcess, $childProcessCount);
        $query->addValuesUpdateProcess($process, $dateTime);
        try {
            $this->writeItem($query);
        } catch (Exception\Pdo\PDOFailed $exception) {
            if ($retry) {
                // First try might fail if two processes are created with the same number at the same time
                sleep(1); // Let the other process complete his transaction
                return $this->writeNewProcess(
                    $process,
                    $dateTime,
                    $parentProcess,
                    $childProcessCount,
                    false,
                    $userAccount
                );
            }
            throw new Exception\Process\ProcessCreateFailed($exception->getMessage());
        }
        (new Slot())->writeSlotProcessMappingFor($process->id);

        $checksum = sha1($process->id . '-' . $userAccount->getId());
        Log::writeLogEntry("CREATE (Process::writeNewProcess) $process $checksum ", $process->id);
        if (!$process->toQueue($dateTime)->withAppointment) {
            (new ExchangeWaitingscope())->writeWaitingTimeCalculated($process->scope, $dateTime);
        }
        return $process;
    }

    /**
     * Fetch a free process ID from DB
     *
     */
    protected function readNewProcessId()
    {
        $query = new Query\Process(Query\Base::SELECT);
        $newProcessId = $this->fetchValue($query->getQueryNewProcessId());
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
        $query
            ->addEntityMapping()
            ->addResolvedReferences(0)
            ->addConditionProcessId($processId);
        $process = $this->fetchOne($query, new Entity());
        return ($process->hasId()) ? array(
            'authName' => $process->getFirstClient()['familyName'],
            'authKey' => $process->authKey
        ) : null;
    }

    protected function readList($statement, $resolveReferences)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $processList = new Collection();
        while ($processData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcessJoins($processData));
            $entity = $this->readResolvedReferences($entity, $resolveReferences);
            $processList->addEntity($entity);
        }
        return $processList;
    }

    /**
     * Read list with following processes in DB
     */
    public function readEntityList($processId, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionProcessIdFollow($processId);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences)->sortByAppointmentDate();
    }

    public function readByWorkstation(\BO\Zmsentities\Workstation $workstation, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionWorkstationId($workstation->id);
        $process = $this->fetchOne($query, new Entity());
        return ($process->hasId()) ? $this->readResolvedReferences($process, $resolveReferences) : null;
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
    public function readProcessListByScopeAndTime(
        $scopeId,
        \DateTimeInterface $dateTime,
        $resolveReferences = 0
    ) {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionAssigned()
            ->addConditionIgnoreSlots()
            ->addConditionTime($dateTime);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }


    /**
     * Read conflictList by scopeId and DateTime
     *
     * @param
     * scopeId
     * dateTime
     *
     * @return Collection processList
     */
    public function readConflictListByScopeAndTime(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $startDate = null,
        \DateTimeInterface $endDate = null,
        \DateTimeInterface $now = null,
        $resolveReferences = 0
    ) {
        $availabilityList = (new Availability())
            ->readAvailabilityListByScope($scope, 0, $startDate, $endDate)
            ->withScope($scope);

        if (! $endDate) {
            $availabilityList = $availabilityList->withDateTime($startDate);
            $endDate = $startDate;
        }
        $currentDate = $startDate;
        $conflictList = $availabilityList->getConflicts($startDate, $endDate);
        while ($currentDate <= $endDate) {
            $query = new Query\Process(Query\Base::SELECT);
            $query
                ->addResolvedReferences($resolveReferences)
                ->addEntityMapping()
                ->addConditionScopeId($scope->getId())
                ->addConditionAssigned()
                ->addConditionIgnoreSlots();
            $query->addConditionTime($currentDate);
            $statement = $this->fetchStatement($query);
            $processList = $this->readList($statement, $resolveReferences);
            $processList = $processList->toQueueList($currentDate)->withoutStatus(['queued'])->toProcessList();
            $conflictList->addList($processList->withOutAvailability($availabilityList));
            $currentDate = $currentDate->modify('+1 day');
        }
        $conflictList = $conflictList->withoutExpiredAppointmentDate($now);
        return $conflictList;
    }


    /**
     * Read processList by scopeId and status
     *
     * @return Collection processList
     */
    public function readProcessListByScopeAndStatus(
        $scopeId,
        $status,
        $resolveReferences = 0,
        $limit = 1000,
        $offset = null
    ) {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionStatus($status, $scopeId)
            ->addLimit($limit, $offset);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    public function readSearch(array $parameter, $resolveReferences = 0, $limit = 100)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionAssigned()
            ->addConditionIgnoreSlots()
            ->addLimit($limit);

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionProcessId($parameter['query']);
                $query->addConditionSearch($parameter['query'], true);
            } else {
                $query->addConditionSearch($parameter['query']);
            }
            unset($parameter['query']);
        }
        if (count($parameter)) {
            $query = $this->addSearchConditions($query, $parameter);
        }

        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    protected function addSearchConditions(Query\Process $query, $parameter)
    {
        if (isset($parameter['processId']) && $parameter['processId']) {
            $query->addConditionProcessId($parameter['processId']);
        }
        if (isset($parameter['name']) && $parameter['name']) {
            $exact = (isset($parameter['exact'])) ? $parameter['exact'] : false;
            $query->addConditionName($parameter['name'], $exact);
        }
        if (isset($parameter['amendment']) && $parameter['amendment']) {
            $query->addConditionAmendment($parameter['amendment']);
        }
        if (isset($parameter['scopeId']) && $parameter['scopeId']) {
            $query->addConditionScopeId($parameter['scopeId']);
        }
        if (isset($parameter['authKey']) && $parameter['authKey']) {
            $query->addConditionAuthKey($parameter['authKey']);
        }
        if (isset($parameter['requestId']) && $parameter['requestId']) {
            $query->addConditionRequestId($parameter['requestId']);
        }
        return $query;
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
            ->addConditionIgnoreSlots()
            ->addConditionScopeId($scopeId);
        $statement = $this->fetchStatement($query);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result['processCount'];
    }

       /**
     * Read processList by mail address
     *
     * @return Collection processList
     */
    public function readProcessListByMailAddress(
        $mailAddress,
        $resolveReferences = 0,
        $limit = 2000
    ) : Collection {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionMail($mailAddress)
            ->addConditionIgnoreSlots()
            ->addLimit($limit)
            ;
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    /**
     * Markiere einen Termin als bestätigt
     *
     * @param
     * process
     *
     * @return Resource Status
     */
    public function updateProcessStatus(
        Entity $process,
        $status,
        \DateTimeInterface $dateTime,
        $resolveReferences = 0,
        $userAccount = null
    ) {
        $process = (new ProcessStatus())
            ->writeUpdatedStatus($process, $status, $dateTime, $resolveReferences, $userAccount);
        return $process;
    }

    /**
     * Löscht einen Termin aus der Datenbank
     * Regulär sollte aber ProcessStatusArchived::writeEntityFinished()
     * oder self::writeBlockedEntity() verwendet werden.
     *
     * @return Resource Status
     */
    public function writeDeletedEntity($processId)
    {
        $query = Query\Process::QUERY_DELETE;
        $status = $this->perform($query, array(
            $processId,
            $processId
        ));
        if ($status) {
            $this->deleteRequestsForProcessId($processId);
            (new Slot())->deleteSlotProcessMappingFor($processId);
        }
        Log::writeLogEntry("DELETE (Process::writeDeletedEntity) $processId ", $processId);
        return $status;
    }

    /**
     * ACHTUNG: Nur noch als Helferfunction vor Refactoring durch MF,
     * damit unittests und zmsappointment wie gewohnt funktionieren
     *
     * @param
     *            processId and authKey
     *
     * @return Resource Status
     */
    public function deleteEntity($processId, $authKey)
    {
        return $this->writeCanceledEntity($processId, $authKey);
    }

    /**
     * Markiere einen Termin als abgesagt
     *
     * @param
     *            processId and authKey
     *
     * @return Resource Status
     */
    public function writeCanceledEntity($processId, $authKey)
    {
        $query = Query\Process::QUERY_CANCELED;
        $this->perform($query, array(
            $processId,
            $authKey,
            $processId
        ));
        Log::writeLogEntry("DELETE (Process::writeCanceledEntity) $processId ", $processId);
        return $this->readEntity($processId, new Helper\NoAuth(), 0);
    }

    /**
     * Markiere einen Termin als dereferenced
     *
     * @param
     *            processId and authKey
     *
     * @return Resource Status
     */
    public function writeBlockedEntity(\BO\Zmsentities\Process $process)
    {
        $amendment = $process->toDerefencedAmendment();
        if (!isset($process->queue['status'])) {
            $process->queue['status'] = $process->status;
        }
        $process->status = 'blocked';
        $query = Query\Process::QUERY_DEREFERENCED;
        $status = $this->perform($query, array(
            $amendment,
            $process->id,
            $process->authKey,
            $process->id
        ));
        if ($status) {
            (new Slot())->deleteSlotProcessMappingFor($process->id);
        }
        Log::writeLogEntry("DELETE (Process::writeBlockedEntity) $process ", $process->id);
        return $status;
    }

    protected function writeRequestsToDb(\BO\Zmsentities\Process $process)
    {
        // Beware of resolveReferences=0 to not delete the existing requests, except for queued processes
        $hasRequests = ($process->requests && count($process->requests));
        if ($hasRequests || 'queued' == $process->status) {
            $this->deleteRequestsForProcessId($process->id);
        }
        if ($hasRequests) {
            $query = new Query\XRequest(Query\Base::INSERT);
            foreach ($process->requests as $request) {
                if ($request->id >= 0) { // allow deleting requests with a -1 request
                    $query->addValues(
                        [
                            'AnliegenID' => $request['id'],
                            'source' => $request['source'],
                            'BuergerID' => $process->id
                        ]
                    );
                    $this->writeItem($query);
                }
            }
        }
    }

    protected function deleteRequestsForProcessId($processId)
    {
        $status = false;
        if (0 < $processId) {
            $query = new Query\XRequest(Query\Base::DELETE);
            $query->addConditionProcessId($processId);
            $status = $this->deleteItem($query);
        }
        return $status;
    }

    public function readExpiredProcessList(
        \DateTimeInterface $expirationDate,
        $limit = 500,
        $resolveReferences = 0,
        $offset = null
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessDeleteInterval($expirationDate)
            ->addConditionIgnoreSlots()
            ->addLimit($limit, $offset);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }

    public function readExpiredProcessListByStatus(
        \DateTimeInterface $expirationDate,
        $status,
        $limit = 500,
        $offset = null,
        $resolveReferences = 0
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessDeleteInterval($expirationDate)
            ->addConditionStatus($status)
            ->addConditionIgnoreSlots()
            ->addLimit($limit, $offset);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }

    public function readExpiredReservationsList(
        \DateTimeInterface $expirationDate,
        $scopeId,
        $limit = 500,
        $resolveReferences = 0
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId)
            ->addConditionIsReserved()
            ->addConditionProcessExpiredIPTimeStamp($expirationDate)
            ->addConditionIgnoreSlots()
            ->addLimit($limit);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }

    public function readNotificationReminderProcessList(
        \DateTimeInterface $dateTime,
        $limit = 500,
        $resolveReferences = 0
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessReminderInterval($dateTime)
            ->addConditionHasTelephone()
            ->addConditionIgnoreSlots()
            ->addLimit($limit);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }

    public function readEmailReminderProcessListByInterval(
        \DateTimeInterface $dateTime,
        $reminderInSeconds,
        $limit = 500,
        $resolveReferences = 0
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessMailReminder($dateTime, $reminderInSeconds)
            ->addConditionAssigned()
            ->addConditionIgnoreSlots()
            ->addLimit($limit);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }

    public function readDeallocateProcessList(
        \DateTimeInterface $now,
        $limit = 500,
        $resolveReferences = 0
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDeallocate($now)
            ->addConditionIgnoreSlots()
            ->addLimit($limit);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }
}
