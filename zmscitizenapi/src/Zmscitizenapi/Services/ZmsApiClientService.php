<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmsentities\Calendar as CalendarEntity;
use BO\Zmsentities\Process as ProcessEntity;

class ZmsApiClientService
{

    public static function getOffices()
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $providerList = $sources->getProviderList() ?? [];

        return $providerList;

    }

    public static function getScopes()
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = $sources->getScopeList() ?? [];

        return $scopeList;

    }

    public static function getServices()
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestList = $sources->getRequestList() ?? [];

        return $requestList;

    }

    public static function getRequestRelationList()
    {

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestRelationList = $sources->getRequestRelationList();

        return $requestRelationList;
    }

    /* Todo add cache methods
    haveCachedSourcesExpired
    getSources

    */

    public static function getFreeDays($providers, $requests, $firstDay, $lastDay)
    {

        $calendar = new CalendarEntity();
        $calendar->firstDay = $firstDay;
        $calendar->lastDay = $lastDay;
        $calendar->providers = $providers;
        $calendar->requests = $requests;

        return \App::$http->readPostResult('/calendar/', $calendar)->getEntity();
    }

    public static function getFreeTimeslots($providers, $requests, $firstDay, $lastDay)
    {

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

        return $result;
    }

    public static function reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts)
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

        $result = \App::$http->readPostResult('/process/status/reserved/', $processEntity);

        return $result->getEntity();
    }

    public static function submitClientData($process)
    {
        $processEntity = new ProcessEntity();
        $processEntity->id = $process['data']['processId'] ?? null;
        $processEntity->authKey = $process['data']['authKey'] ?? null;
        $processEntity->appointments = $process['appointments'] ?? [];
        $processEntity->clients = $process['clients'] ?? [];
        $processEntity->scope = $process['data']['scope'] ?? null;
        $processEntity->customTextfield = $process['customTextfield'] ?? null;
        $processEntity->lastChange = $process['lastChange'] ?? time();

        if (isset($process['queue'])) {
            $processEntity->queue = $process['queue'];
        }

        $processEntity->createIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $processEntity->createTimestamp = time();

        $url = "/process/{$processEntity->id}/{$processEntity->authKey}/";

        try {
            $result = \App::$http->readPostResult($url, $processEntity);
            return $result->getEntity();
        } catch (\Exception $e) {
            $exceptionName = json_decode(json_encode($e), true)['template'] ?? null;
            if ($exceptionName === 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail') {
                $exception = [
                    'exception' => 'tooManyAppointmentsWithSameMail'
                ];
                return $exception;
            }
        }

    }

    public function preconfirmProcess($process)
    {
        $url = '/process/status/preconfirmed/';
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function confirmProcess($process)
    {
        $url = '/process/status/confirmed/';
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function cancelAppointment($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/";
        return \App::$http->readDeleteResult($url, $process)->getEntity();
    }

    public function sendConfirmationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/confirmation/mail/";
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function sendPreconfirmationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/preconfirmation/mail/";
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function sendCancelationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/delete/mail/";
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public static function getProcessById($processId, $authKey)
    {
        $resolveReferences = 2;
        $process = \App::$http->readGetResult("/process/{$processId}/{$authKey}/", [
            'resolveReferences' => $resolveReferences
        ])->getEntity();

        return $process;
    }

}