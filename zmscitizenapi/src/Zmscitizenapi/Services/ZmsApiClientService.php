<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Services\ExceptionService;
use BO\Zmsentities\Calendar as Calendar;
use BO\Zmsentities\Process as Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Collection\ScopeList;

class ZmsApiClientService
{
    
    private static function fetchSourceData(): mixed
    {
        try {
            return \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
                'resolveReferences' => 2,
            ])->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getOffices(): ProviderList
    {
        try {
            $sources = self::fetchSourceData();

            $providerList = $sources->getProviderList() ?? new ProviderList();

            return $providerList;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getScopes(): ScopeList
    {
        try {
            $sources = self::fetchSourceData();

            $scopeList = $sources->getScopeList() ?? new ScopeList();

            return $scopeList;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getServices(): RequestList
    {
        try {
            $sources = self::fetchSourceData();

            $requestList = $sources->getRequestList() ?? new RequestList();

            return $requestList;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getRequestRelationList(): RequestRelationList
    {
        try {
            $sources = self::fetchSourceData();

            $requestRelationList = $sources->getRequestRelationList() ?? new RequestRelationList();

            return $requestRelationList;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getFreeDays(ProviderList $providers, RequestList $requests, array $firstDay, array $lastDay): Calendar
    {
        try {
            $calendar = new Calendar();
            $calendar->firstDay = $firstDay;
            $calendar->lastDay = $lastDay;
            $calendar->providers = $providers;
            $calendar->requests = $requests;

            $result = \App::$http->readPostResult('/calendar/', $calendar);

            return $result->getEntity() ?? new Calendar();

        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getFreeTimeslots(ProviderList $providers, RequestList $requests, array $firstDay, array $lastDay): ProcessList
    {
        try {
            $calendar = new Calendar();
            $calendar->firstDay = $firstDay;
            $calendar->lastDay = $lastDay;
            $calendar->providers = $providers;
            $calendar->requests = $requests;

            $result = \App::$http->readPostResult('/process/status/free/', $calendar);

            if (!$result || !method_exists($result, 'getCollection')) {
                throw new \UnexpectedValueException('Invalid response from API');
            }

            return $result->getCollection();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): Process
    {
        try {
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
            $processEntity->appointments = $appointmentProcess->appointments ?? [];
            $processEntity->authKey = $appointmentProcess->authKey ?? null;
            $processEntity->clients = $appointmentProcess->clients ?? [];
            $processEntity->scope = $appointmentProcess->scope ?? null;
            $processEntity->requests = $requests;
            $processEntity->lastChange = $appointmentProcess->lastChange ?? time();
            $processEntity->createIP = ClientIpHelper::getClientIp();
            $processEntity->createTimestamp = time();

            if (isset($appointmentProcess->queue)) {
                $processEntity->queue = $appointmentProcess->queue;
            }

            $result = \App::$http->readPostResult('/process/status/reserved/', $processEntity);

            return $result->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function submitClientData(Process $process): Process
    {
        $url = "/process/{$process->id}/{$process->authKey}/";

        try {
            $result = \App::$http->readPostResult($url, $process);
            return $result->getEntity();

        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public function preconfirmProcess(Process $process): Process
    {
        try {
            $url = '/process/status/preconfirmed/';
            return \App::$http->readPostResult($url, $process)->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public function confirmProcess(Process $process): Process
    {
        try {
            $url = '/process/status/confirmed/';
            return \App::$http->readPostResult($url, $process)->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public function cancelAppointment(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/";
            return \App::$http->readDeleteResult($url, $process)->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public function sendConfirmationEmail(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/confirmation/mail/";
            return \App::$http->readPostResult($url, $process)->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public function sendPreconfirmationEmail(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/preconfirmation/mail/";
            return \App::$http->readPostResult($url, $process)->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public function sendCancelationEmail(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/delete/mail/";
            return \App::$http->readPostResult($url, $process)->getEntity();
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getProcessById(int $processId, string $authKey): Process
    {
        try {
            $resolveReferences = 2;
            $process = \App::$http->readGetResult("/process/{$processId}/{$authKey}/", [
                'resolveReferences' => $resolveReferences
            ])->getEntity();

            return $process;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getScopesByProviderId(string $source, string|int $providerId): ScopeList
    {
        try {
            $scopeList = self::getScopes() ?? new ScopeList();
            return $scopeList->withProviderID($source, (string) $providerId);
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

}
