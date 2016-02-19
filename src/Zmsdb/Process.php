<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use BO\Zmsdb\Helper\ProcessStatus as Status;
use \BO\Zmsdb\Query\SlotList;

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
        }
        else {
            $processId = $this->getNewProcessId();
        }
        $query->addConditionProcessId($processId);

        if(array_key_exists('authKey', $processData) && $processData['authKey'] != 0){
            $authKey = $processData['authKey'];
            $query->addConditionAuthKey($authKey);
        } else {
            $authKey = substr(md5(rand()), 0, 4);
        }

        $process['id'] = $processId;
        $process['authKey'] = $authKey;
        $values = $query->reverseEntityMapping($process);

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

        if(null === $xrequests){
            $query = new Query\XRequest(Query\Base::INSERT);
            foreach($requests as $request){
                $query->addValues([
                    'AnliegenID' => $request['id'],
                    'BuergerID' => $processId
                ]);
                $this->writeItem($query);
            }
        }
        else {
            foreach($xrequests as $xrequest){
                $query = new Query\XRequest(Query\Base::UPDATE);
                $query->addConditionXRequestId($xrequest['id']);
                $query->addValues([
                    'AnliegenID' => $requests[$xrequest['id']],
                    'BuergerID' => $processId
                ]);
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
            $query->addValues([
                'BuergerID' => 'SELECT A.BuergerID+1 AS nextid
                    FROM buerger A
                    LEFT JOIN buerger B on A.BuergerID+1 = B.BuergerID
                    WHERE B.BuergerID IS NULL AND A.BuergerID > 10000
                    ORDER BY A.BuergerID LIMIT 1',
                'IPTimeStamp' => $dateTime->getTimestamp()
            ]);
        } else {
            $query->addValues([
                'BuergerID' => NULL
            ]);
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
    public function readFreeAppointments(\BO\Zmsentities\Calendar $calendar)
    {
        $process = new Entity();
        $process['processing'] = [];
        $process['processing']['slotlist'] = new SlotList();
        $process = $this->readResolvedRequests($calendar['requests'], $process);
        $process = $this->readResolvedProviders($calendar['providers'], $process);
        $process = $this->readResolvedDay($calendar['firstDay'], $process);
        unset($process['processing']);
        return $process;
    }

    protected function readResolvedRequests(Array $requests, Entity $process)
    {
        $requestReader = new Request($this->getWriter(), $this->getReader());
        if (! isset($process['processing']['slotinfo'])) {
            $process['processing']['slotinfo'] = [];
        }
        foreach ($requests as $key => $request) {
            $request = $requestReader->readEntity('dldb', $request['id']);
            $process['requests'][$key] = $request;
            foreach ($requestReader->readSlotsOnEntity($request) as $slotinfo) {
                if (! isset($process['processing']['slotinfo'][$slotinfo['provider__id']])) {
                    $process['processing']['slotinfo'][$slotinfo['provider__id']] = 0;
                }
                $process['processing']['slotinfo'][$slotinfo['provider__id']] += $slotinfo['slots'];
            }
        }
        return $process;
    }

    protected function readResolvedProviders(Array $providers, Entity $process)
    {
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
        $providerReader = new Provider($this->getWriter(), $this->getReader());
        foreach ($providers as $key => $provider) {
            $process['providers'][$key] = $providerReader->readEntity('dldb', $provider['id']);
            $scopeList = $scopeReader->readByProviderId($provider['id']);
            foreach ($scopeList as $key => $scope) {
                $process['scopes'][$key] = $scope;
            }
        }
        return $process;
    }

    protected function readResolvedDay(Array $firstDay, Entity $process)
    {
        $query = SlotList::getQuery();
        $statement = $this->getReader()->prepare($query);
        $process['appointments'] = array();

        $date = \DateTime::createFromFormat('Y-m-d', $firstDay['year'] . '-' . $firstDay['month'] . '-' . $firstDay['day']);

        foreach ($process->scopes as $scope) {
            $statement->execute(SlotList::getParameters($scope['id'], $date));
            while ($slotData = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $appointment = $this->addAppointmentsToProcess($process, $slotData, $date);
                if (null !== $appointment) {
                    $process['appointments'][] = $appointment;
                }
            }
        }
        return $process;
    }

    protected function addAppointmentsToProcess($process, array $slotData, \DateTime $date)
    {
        $appointment = null;
        $slotDate = \DateTime::createFromFormat('Y-m-d', $slotData['year'] . "-" . $slotData['month'] . '-' . $slotData['day']);
        if ($slotDate->format('Y-m-d') == $date->format('Y-m-d')) {
            $scopeReader = new Scope($this->getWriter(), $this->getReader());
            $scope = $scopeReader->readEntity($slotData['appointment__scope__id'], 1);
            $appointment = new \BO\Zmsentities\Appointment();
            $appointment->addDate($slotData['appointment__date']);
            $appointment->scope = $scope;
        }
        return $appointment;
    }
}
