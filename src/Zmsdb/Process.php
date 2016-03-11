<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use BO\Zmsdb\Helper\ProcessStatus as Status;

class Process extends Base
{

    public function readEntity($processId, $authKey, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessId($processId)
            ->addConditionAuthKey($authKey);
        //\App::$log->debug($query->getSql());
        // var_dump($this->fetchOne($query, new Entity()));
        $process = $this->fetchOne($query, new Entity());
        $process['requests'] = (new Request())->readRequestByProcessId($processId);
        $process['status'] = (new Status())->readProcessStatus($processId, $authKey);
        return $process;
    }

    public function updateEntity(\BO\Zmsentities\Process $process)
    {
        //\App::$log->debug('UPDATE ENTITY');
        $query = new Query\Process(Query\Base::UPDATE);

        if (array_key_exists('id', $process) && $process['id'] != 0) {
            $processId = $process['id'];
        } else {
            $processId = $this->getNewProcessId();
        }
        $query->addConditionProcessId($processId);
        if (array_key_exists('authKey', $process) && $process['authKey'] != 0) {
            $authKey = $process['authKey'];
            $query->addConditionAuthKey($authKey);
        } else {
            $authKey = substr(md5(rand()), 0, 4);
        }

        $process['id'] = $processId;
        $process['authKey'] = $authKey;
        $values = $query->reverseEntityMapping($process);
        $query->addValues($values);
        $this->writeItem($query, 'process', $query::TABLE);
        $this->writeRequestsToDb($processId, $process['requests']);

        $process = $this->readEntity($processId, $authKey, 1);
        $process['status'] = (new Status())->readProcessStatus($processId, $authKey);
        return $process;
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
            $query = Query\XRequest::QUERY_DELETE;
            $statement = $this->getWriter()->prepare($query);
            $status = $status = $statement->execute(
                array(
                $processId
                )
            );
        }
            return $status;
    }

    public function writeRequestsToDb($processId, $requests)
    {
        $checkRequests = (new Request())->readRequestByProcessId($processId);
        if (null === $checkRequests) {
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
        } else {
            foreach ($requests as $request) {
                $query = new Query\XRequest(Query\Base::UPDATE);
                $query->addConditionProcessId($processId);
                $query->addValues(
                    [
                        'AnliegenID' => $request['id']
                    ]
                );
                $this->writeItem($query);
            }
        }

    }

    public function getNewProcessId()
    {
        $query = new Query\Process(Query\Base::INSERT);
        $lock = $this->getLock();
        if ($lock == 1) {
            $dateTime = new \DateTime();
            $query->addValues(
                [
                'BuergerID' => 'SELECT A.BuergerID+1 AS nextid
                    FROM buerger A
                    LEFT JOIN buerger B on A.BuergerID+1 = B.BuergerID
                    WHERE B.BuergerID IS NULL AND A.BuergerID > 10000
                    ORDER BY A.BuergerID LIMIT 1',
                'IPTimeStamp' => $dateTime->getTimestamp()
                ]
            );
        } else {
            $query->addValues(
                [
                'BuergerID' => null
                ]
            );
        }

        $this->writeItem($query);
        $lastInsertId = $this->getWriter()
            ->lastInsertId();
        $this->releaseLock();
        return $lastInsertId;
    }

    public function getLock()
    {
        return $this->getReader()
            ->fetchValue('SELECT GET_LOCK("AutoIncWithOldNum", 2)');
    }

    public function releaseLock()
    {
        return $this->getReader()
            ->fetchValue('SELECT RELEASE_LOCK("AutoIncWithOldNum")');
    }

    /* prüfen ob das benötigt wird begin */
    public function readFreeProcesses(\BO\Zmsentities\Calendar $calendar)
    {
        $resolvedCalendar = new Calendar();
        $calendar = $resolvedCalendar->readResolvedEntity($calendar, true, false);
        return $calendar;
    }
}
