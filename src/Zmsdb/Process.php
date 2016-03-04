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
        $status = new Status();
        $process['status'] = $status->readProcessStatus($processId, $authKey);
        return $process;
    }

    public function updateEntity($processData)
    {
        $query = new Query\Process(Query\Base::UPDATE);
        $process = new Entity($processData);

        if (array_key_exists('id', $processData) && $processData['id'] != 0) {
            $processId = $processData['id'];
        } else {
            $processId = $this->getNewProcessId();
        }
        $query->addConditionProcessId($processId);

        if (array_key_exists('authKey', $processData) && $processData['authKey'] != 0) {
            $authKey = $processData['authKey'];
            $query->addConditionAuthKey($authKey);
        } else {
            $authKey = substr(md5(rand()), 0, 4);
        }

        $process['id'] = $processId;
        $process['authKey'] = $authKey;
        $values = $query->reverseEntityMapping($process);
        \App::$log->debug('values', [$values]);
        $query->addValues($values);
        $this->writeItem($query, 'process', $query::TABLE);
        $this->writeXRequestsToDb($process['id'], $process['requests']);

        $process = $this->readEntity($processId, $authKey, 1);
        $status = new Status();
        $process['status'] = $status->readProcessStatus($processId, $authKey);
        return $process;
    }

    public function writeXRequestsToDb($processId, $requests)
    {
        $xrequests = (new Request())->readXRequestByProcessId($processId);

        if (null === $xrequests) {
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
            foreach ($xrequests as $xrequest) {
                $query = new Query\XRequest(Query\Base::UPDATE);
                $query->addConditionXRequestId($xrequest['id']);
                $query->addValues(
                    [
                    'AnliegenID' => $requests[$xrequest['id']],
                    'BuergerID' => $processId
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
        $lastInsertId = $this->getWriter()->lastInsertId();
        $this->releaseLock();
        return $lastInsertId;
    }

    public function getLock()
    {
        return $this->getReader()->fetchValue('SELECT GET_LOCK("AutoIncWithOldNum", 2)');
    }

    public function releaseLock()
    {
        return $this->getReader()->fetchValue('SELECT RELEASE_LOCK("AutoIncWithOldNum")');
    }

    /* prüfen ob das benötigt wird begin */
    public function readFreeProcesses(\BO\Zmsentities\Calendar $calendar)
    {
        $resolvedCalendar = new Calendar();
        $calendar = $resolvedCalendar->readResolvedEntity($calendar, true, false);
        return $calendar;
    }
}
