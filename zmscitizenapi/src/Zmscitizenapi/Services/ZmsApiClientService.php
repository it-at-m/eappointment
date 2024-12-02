<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmsentities\Calendar as Calendar;
use BO\Zmsentities\Process as Process;
use \BO\Zmsentities\Collection\ProcessList;
use \BO\Zmsentities\Collection\ProviderList;
use \BO\Zmsentities\Collection\RequestList;
use \BO\Zmsentities\Collection\RequestRelationList;
use \BO\Zmsentities\Collection\ScopeList;

class ZmsApiClientService
{

    /* Todo add cache methods
    haveCachedSourcesExpired
    getSources

    */

    public static function getOffices(): ProviderList
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $providerList = new ProviderList();
        $providerList = $sources->getProviderList() ?? $providerList;

        return $providerList;

    }

    public static function getScopes(): ScopeList
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = new ScopeList();
        $scopeList = $sources->getScopeList() ?? $scopeList;

        return $scopeList;

    }

    public static function getServices(): RequestList
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestList = new RequestList();
        $requestList = $sources->getRequestList() ?? $requestList;

        return $requestList;

    }

    public static function getRequestRelationList(): RequestRelationList
    {

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestRelationList = new RequestRelationList();
        $requestRelationList = $sources->getRequestRelationList() ?? $requestRelationList;

        return $requestRelationList;
    }

    public static function getFreeDays(ProviderList $providers, RequestList $requests, array $firstDay, array $lastDay): Calendar
    {
        $calendar = new Calendar();
        $calendar->firstDay = $firstDay;
        $calendar->lastDay = $lastDay;
        $calendar->providers = $providers;
        $calendar->requests = $requests;

        $result = new Calendar();
        $result = \App::$http->readPostResult('/calendar/', $calendar)->getEntity() ?? $result;
        return $result;
    }

    public static function getFreeTimeslots(ProviderList $providers, RequestList $requests, array $firstDay, array $lastDay): ProcessList
    {

        $calendar = new Calendar();
        $calendar->firstDay = $firstDay;
        $calendar->lastDay = $lastDay;
        $calendar->providers = $providers;
        $calendar->requests = $requests;


        $result = \App::$http->readPostResult('/process/status/free/', $calendar);
        if (!$result || !method_exists($result, 'getCollection')) {
            throw new \Exception('Invalid response from API');
        }
        return $result->getCollection();
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): Process
    {
        $requests = [];

        foreach ($serviceIds as $index => $serviceId) {
            $count = intval($serviceCounts[$index]);
            for ($i = 0; $i < $count; $i++) {
                $requests[] = [
                    'id' => $serviceId,
                    'source' => \App::$source_name
                ];
            }
        }

        $processEntity = new Process();
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

        $result = new Process();
        $result = \App::$http->readPostResult('/process/status/reserved/', $processEntity);

        return $result->getEntity();
    }

    public static function submitClientData(Process $process): Process|array
    {
        $processEntity = new Process();
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
            } else {
                throw $e;
            }
        }
    }

    public function preconfirmProcess(?Process $process): Process
    {
        $url = '/process/status/preconfirmed/';
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function confirmProcess(?Process $process): Process
    {
        $url = '/process/status/confirmed/';
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function cancelAppointment(?Process $process): Process
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/";
        return \App::$http->readDeleteResult($url, $process)->getEntity();
    }

    public function sendConfirmationEmail(?Process $process): Process
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/confirmation/mail/";
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function sendPreconfirmationEmail(?Process $process): Process
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/preconfirmation/mail/";
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public function sendCancelationEmail(?Process $process): Process
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/delete/mail/";
        return \App::$http->readPostResult($url, $process)->getEntity();
    }

    public static function getProcessById(?int $processId, ?string $authKey): Process
    {
        $resolveReferences = 2;
        $process = new Process();
        $process = \App::$http->readGetResult("/process/{$processId}/{$authKey}/", [
            'resolveReferences' => $resolveReferences
        ])->getEntity();

        return $process;
    }

}