<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmsentities\Calendar as CalendarEntity;
use BO\Zmsentities\Process as ProcessEntity;

class ProcessService
{
    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getProcessById($processId, $authKey)
    {
        $resolveReferences = 2;
        $process = $this->httpClient->readGetResult("/process/{$processId}/{$authKey}/", [
            'resolveReferences' => $resolveReferences
        ])->getEntity();

        return $process;
    }

    public function reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts)
    {
        $requests = [];
    
        foreach ($serviceIds as $index => $serviceId) {
            $count = intval($serviceCounts[$index]);
            for ($i = 0; $i < $count; $i++) {
                $requests[] = [
                    'id' => $serviceId,
                    'source' => 'dldb'
                ];
            }
        }

        $processEntity = new ProcessEntity();
    
        $processEntity->appointments = $appointmentProcess['appointments'] ?? [];
        $processEntity->authKey = $appointmentProcess['authKey'] ?? null;
        $processEntity->clients = $appointmentProcess['clients'] ?? [];
    
        $processEntity->scope = $appointmentProcess['scope'] ?? null;
        $processEntity->requests = $requests;
        $processEntity->lastChange = $appointmentProcess['lastChange'] ?? time();
    

        $processEntity->createIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $processEntity->createTimestamp = time();
    
        if (isset($appointmentProcess['queue'])) {
            $processEntity->queue = $appointmentProcess['queue'];
        }
    
        $result = $this->httpClient->readPostResult('/process/status/reserved/', $processEntity);
    
        return $result->getEntity();
    }
    

    public function submitClientData($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function preconfirmProcess($process)
    {
        $url = '/process/status/preconfirmed/';
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function confirmProcess($process)
    {
        $url = '/process/status/confirmed/';
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function cancelAppointment($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/";
        return $this->httpClient->readDeleteResult($url, $process)->getEntity();
    }

    public function sendConfirmationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/confirmation/mail/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function sendPreconfirmationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/preconfirmation/mail/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function sendCancelationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/delete/mail/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function getFreeDays($providers, $requests, $firstDay, $lastDay)
    {
        $requestUrl = '/calendar/';
        $dataPayload = [
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
            'providers' => $providers,
            'requests' => $requests,
        ];

        return $this->httpClient->readPostResult($requestUrl, $dataPayload)->getEntity();
    }

    public function getFreeTimeslots($providers, $requests, $firstDay, $lastDay)
    {
        $requestUrl = '/process/status/free/';
        $dataPayload = [
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
            'providers' => $providers,
            'requests' => $requests,
        ];

        $calendar = new CalendarEntity();
        $calendar->firstDay = $firstDay;
        $calendar->lastDay = $lastDay;
        $calendar->providers = $providers;
        $calendar->requests = $requests;


        $result = \App::$http->readPostResult('/process/status/free/', $calendar);
        if (!$result || !method_exists($result, 'getCollection')) {
            throw new \Exception('Invalid response from API');
        }

        $psr7Response = $result->getResponse();
        $responseBody = (string) $psr7Response->getBody();        

        return json_decode($responseBody, true);
    }





}
