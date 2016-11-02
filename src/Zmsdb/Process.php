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
        $process['requests'] = (new Request())->readRequestByProcessId($processId, $resolveReferences);
        $process['status'] = (new Status())->readProcessStatus($processId, $authKey);
        $process['scope'] = (new Scope())->readEntity($process->getScopeId(), $resolveReferences);
        $process = $this->addDldbData($process, $resolveReferences);
        return $process;
    }

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
    public function readProcessListByScopeId($scopeId)
    {
        $processList = new Collection();
        $query = new Query\Process(Query\Base::SELECT);
        $query->addEntityMapping()->addConditionScopeId($scopeId);
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
            $mail =  new Mail();
            $mail->deleteEntityByProcess($processId);
            $notification =  new Notification();
            $notification->deleteEntityByProcess($processId);
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

    public function writeNewProcess($forceUnLocked = false)
    {
        $query = new Query\Process(Query\Base::INSERT);
        $lock = $this->getLock($query);
        $dateTime = new \DateTime();
        if ($lock == 1 && false === $forceUnLocked) {
            $autoincrement = '';
            $query->addValues(
                [
                'BuergerID' => $this->getNewProcessId($query),
                'IPTimeStamp' => (int) $dateTime->getTimestamp(),
                'absagecode' => substr(md5(rand()), 0, 4)
                ]
            );
        } else {
            $autoincrement = '(autoincrement)';
            $query->addValues(
                [
                'BuergerID' => null,
                'IPTimeStamp' => (int) $dateTime->getTimestamp(),
                'absagecode' => substr(md5(rand()), 0, 4)
                ]
            );
        }
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()
            ->lastInsertId();
        $this->releaseLock($query);
        Log::writeLogEntry("CREATE (Process::writeNewProcess) process#$lastInsertId $autoincrement ", $lastInsertId);
        return $lastInsertId;
    }

    public function getNewProcessId($query)
    {
        $newProcessId = 100000;
        if ($this->getReader()->fetchValue($query->getFirstSixDigitProcessId())) {
            $newProcessId = $this->getReader()->fetchValue($query->getQueryNewProcessId());
        }
        return $newProcessId;
    }

    public function getLock($query)
    {
        return $this->getReader()
            ->fetchValue($query::QUERY_SET_LOCK);
    }

    public function releaseLock($query)
    {
        return $this->getReader()
            ->fetchValue($query::QUERY_RELEASE_LOCK);
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
