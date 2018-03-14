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

    public function updateEntity(\BO\Zmsentities\Process $process, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process['id']);
        $query->addConditionAuthKey($process['authKey']);
        $query->addValuesUpdateProcess($process, $dateTime);
        $this->writeItem($query);
        $this->writeRequestsToDb($process);
        $process = $this->readEntity($process->id, $process->authKey, $resolveReferences);
        Log::writeLogEntry("UPDATE (Process::updateEntity) $process ", $process->id);
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
        \DateTimeInterface $dateTime,
        $parentProcess = 0,
        $childProcessCount = 0
    ) {
        $query = new Query\Process(Query\Base::INSERT);
        $process->id = $this->readNewProcessId();
        $process->setRandomAuthKey();
        $process->createTimestamp = $dateTime->getTimestamp();
        $query->addValuesNewProcess($process, $parentProcess, $childProcessCount);
        $query->addValuesUpdateProcess($process, $dateTime);
        $this->writeItem($query);
        Log::writeLogEntry("CREATE (Process::writeNewProcess) $process ", $process->id);
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
        if ($this->getWriter()->fetchValue($query->getLockProcessId())) {
            $newProcessId = $this->getWriter()->fetchValue($query->getQueryNewProcessId());
        } else {
            throw new Exception\Process\ProcessTimeout();
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
    public function readProcessListByScopeAndTime($scopeId, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionAssigned()
            ->addConditionTime($dateTime);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    /**
     * Read processList by scopeId and status
     *
     * @param
     * scopeId
     *
     * @return Collection processList
     */
    public function readProcessListByScopeAndStatus($scopeId, $status, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionStatus($status, $scopeId)
            ->addLimit(1000);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    public function readSearch($queryString, $resolveReferences = 0, $limit = 100)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionAssigned()
            ->addLimit($limit)
            ;
        if (preg_match('#^\d+$#', $queryString)) {
            $query->addConditionProcessId($queryString);
        } else {
            $query->addConditionSearch($queryString);
        }
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
    public function updateProcessStatus(Entity $process, $status, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        //\App::$log->debug('UPDATE STATUS');
        $process = (new ProcessStatus())->readUpdatedStatus($process, $status, $dateTime, $resolveReferences);
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
        $statement = $this->getWriter()->prepare($query);
        $status = $statement->execute(
            array(
                $processId,
                $processId
            )
        );
        if ($status) {
            $this->deleteRequestsForProcessId($processId);
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
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(
            array(
                $processId,
                $authKey,
                $processId
            )
        );
        Log::writeLogEntry("DELETE (Process::writeCanceledEntity) $processId ", $processId);
        return $this->readEntity($processId, $authKey, 0);
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
        if (! isset($process->queue['status'])) {
            $process->queue['status'] = $process->status;
        }
        $process->status = 'blocked';
        $query = Query\Process::QUERY_DEREFERENCED;
        $statement = $this->getWriter()->prepare($query);
        $status = $statement->execute(
            array(
                $amendment,
                $process->id,
                $process->authKey,
                $process->id
            )
        );
        Log::writeLogEntry("DELETE (Process::writeBlockedEntity) $process ", $process->id);
        return $status;
    }

    protected function writeRequestsToDb(\BO\Zmsentities\Process $process)
    {
        if ($process->requests && count($process->requests)) {
            // Beware of resolveReferences=0 to not delete the existing requests
            $this->deleteRequestsForProcessId($process->id);
            $query = new Query\XRequest(Query\Base::INSERT);
            foreach ($process->requests as $request) {
                if ($request->id >= 0) { // allow deleting requests with a -1 request
                    $query->addValues(
                        [
                            'AnliegenID' => $request['id'],
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
        if (0 < $processId) {
            $query =  new Query\XRequest(Query\Base::DELETE);
            $query->addConditionProcessId($processId);
            return $this->deleteItem($query);
        }
    }

    public function readExpiredProcessList(
        \DateTimeInterface $expirationDate,
        $limit = 500,
        $resolveReferences = 0
    ) {
        $selectQuery = new Query\Process(Query\Base::SELECT);
        $selectQuery
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessDeleteInterval($expirationDate)
            ->addConditionIgnoreSlots()
            ->addLimit($limit);
        $statement = $this->fetchStatement($selectQuery);
        return $this->readList($statement, $resolveReferences);
    }
}
