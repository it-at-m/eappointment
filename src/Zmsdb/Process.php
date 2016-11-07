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
        $process['requests'] = (new Request())->readRequestByProcessId($processId, $resolveReferences);
        $process['status'] = (new Status())->readProcessStatus($processId, $authKey);
        $process['scope'] = (new Scope())->readEntity($process->getScopeId(), $resolveReferences);
        $process = $this->addDldbData($process, $resolveReferences);
        return $process;
    }

    /*
    public function updateEntity(\BO\Zmsentities\Process $process)
    {
        $query = new Query\Process(Query\Base::UPDATE);
        if (array_key_exists('id', $process) && !empty($process['id'])) {
            $processId = $process['id'];
        } else {
            $processId = $this->writeNewProcess();
        }
        if (array_key_exists('authKey', $process) && !empty($process['authKey'])) {
            $authKey = $process['authKey'];
        } else {
            $authKey = $this->readAuthKeyByProcessId($processId);
        }

        $query->addConditionProcessId($processId);
        $query->addConditionAuthKey($authKey);

        $values = $query->reverseEntityMapping($process);
        $query->addValues($values);
        $this->writeItem($query);
        $this->writeRequestsToDb($processId, $process['requests']);

        $process = $this->readEntity($processId, $authKey);
        $process['status'] = (new Status())->readProcessStatus($processId, $authKey);
        Log::writeLogEntry("UPDATE (Process::updateEntity) $process ", $processId);
        return $process;
    }
    */

    public function updateEntity(\BO\Zmsentities\Process $process)
    {
        $query = new Query\Process(Query\Base::UPDATE);
        $query->addConditionProcessId($process['id']);
        $query->addConditionAuthKey($process['authKey']);

        $values = $query->reverseEntityMapping($process);
        $query->addValues($values);
        $this->writeItem($query);
        $this->writeRequestsToDb($process->id, $process->requests);

        $process = $this->readEntity($process->id, $process->authKey);
        $process->status = (new Status())->readProcessStatus($process->id, $process->authKey);

        Log::writeLogEntry("UPDATE (Process::updateEntity) $process ", $process->id);
        return $process;
    }

    /**
     * Insert a new process if there are free slots
     *
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
            throw new Exception\ProcessReserveFailed("Already reserved");
        }
        $slotList = (new Slot)->readByAppointment($appointment);
        if (!$slotList->isAvailableForAll($slotType)) {
            throw new Exception\ProcessReserveFailed("Could not reserve multiple slots");
        }
        foreach ($slotList as $slot) {
            if ($process->id > 99999) {
                $newProcess = clone $process;
                $newProcess->getFirstAppointment()->setTime($slot->time);
                $this->writeNewProcess($newProcess, $process->id);
            } elseif ($process->id === 0) {
                $process = $this->writeNewProcess($process, 0, count($slotList) - 1);
            } else {
                throw new \Exception("SQL UPDATE error on inserting new $process on $slot");
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
        $parentProcess = 0,
        $childProcessCount = 0
    ) {
        $query = new Query\Process(Query\Base::INSERT);
        $process->id = $this->readNewProcessId();
        $process->setRandomAuthKey();
        $values = $query->reverseEntityMapping($process);
        $query->addValues($values);
        $query->addValuesNewProcess($process, $parentProcess, $childProcessCount);
        $this->writeItem($query);
        Log::writeLogEntry("CREATE (Process::writeNewProcess) process#{$process->id} ", $process->id);
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
        return ('' != $process['authKey']) ? $process['authKey'] : null;
    }

    /**
     * Read processList by scopeId
     *
     * @param
     * scopeId
     *
     * @return String authKey
     */
    public function readProcessListByScopeAndTime($scopeId, \DateTimeInterface $now)
    {
        $processList = new Collection();
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionTime($now);
        $statement = $this->fetchStatement($query);
        while ($processData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcess($processData));
            if ($entity instanceof Entity) {
                $processList->addEntity($entity);
            }
        }
        return $processList;
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
            //delete mail and notification from queue by processId
            //commented because mails with delete messages will removed too
            /*
            $mail =  new Mail();
            $mail->deleteEntityByProcess($processId);
            $notification =  new Notification();
            $notification->deleteEntityByProcess($processId);
            */
        }
        Log::writeLogEntry("DELETE (Process::deleteEntity) $processId ", $processId);
        return $status;
    }

    public function writeRequestsToDb($processId, $requests)
    {
        $deleteQuery = new Query\XRequest(Query\Base::DELETE);
        $deleteQuery->addConditionProcessId($processId);
        $this->deleteItem($deleteQuery);
        $query = new Query\XRequest(Query\Base::INSERT);
        foreach ($requests as $request) {
            $query->addValues(
                [
                    'AnliegenID' => $request['id'],
                    'BuergerID' => $processId
                ]
            );
            $this->writeItem($query);
        }
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
            ->addConditionIsReserved();
        $resultData = $this->fetchList($query, new Entity());
        foreach ($resultData as $process) {
            if (2 == $resolveReferences) {
                $process['requests'] = (new Request())->readRequestByProcessId($process->id, $resolveReferences);
                $process['status'] = (new Status())->readProcessStatus($process->id, $process->authKey);
                $process['scope'] = (new Scope())->readEntity($process->getScopeId(), $resolveReferences);
            }
            if ($process instanceof Entity) {
                $processList->addEntity($process);
            }
        }
        return $processList;
    }

    protected function addDldbData($process, $resolveReferences)
    {
        if (isset($process['scope']['provider'])) {
            $provider = $process['scope']['provider'];
            if ($resolveReferences >= 2 && $provider['source'] == 'dldb') {
                $process['scope']['provider']['data'] = Helper\DldbData::readExtendedProviderData(
                    $provider['source'],
                    $provider['id']
                );
            }
        }
        return $process;
    }
}
