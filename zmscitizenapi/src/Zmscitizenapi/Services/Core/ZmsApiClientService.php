<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use BO\Zmscitizenapi\Services\Core\ExceptionService;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Process;
use BO\Zmsentities\Source;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Collection\ScopeList;

class ZmsApiClientService
{

    private static function fetchSourceData(): Source
    {
        $cacheKey = 'source_' . \App::$source_name;
    
        if (\App::$cache && ($data = \App::$cache->get($cacheKey))) {
            return $data;
        }

        $result = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ]);

        $entity = $result?->getEntity();
        if (!$entity instanceof Source) {
            return new Source();
        }

        if (\App::$cache) {
            \App::$cache->set($cacheKey, $entity, \App::$SOURCE_CACHE_TTL);
            LoggerService::logInfo('Cache set', [
                'key' => $cacheKey,
                'ttl' => \App::$SOURCE_CACHE_TTL,
                'entity_type' => get_class($entity)
            ]);
        }

        return $entity;
    }

    public static function getOffices(): ProviderList
    {
        try {
            $sources = self::fetchSourceData();
            $list = $sources?->getProviderList();
            if (!$list instanceof ProviderList) {
                return new ProviderList();
            }
            return $list;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getScopes(): ScopeList
    {
        try {
            $sources = self::fetchSourceData();
            $list = $sources?->getScopeList();
            if (!$list instanceof ScopeList) {
                return new ScopeList();
            }
            return $list;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getServices(): RequestList
    {
        try {
            $sources = self::fetchSourceData();
            $list = $sources?->getRequestList();
            if (!$list instanceof RequestList) {
                return new RequestList();
            }
            return $list;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getRequestRelationList(): RequestRelationList
    {
        try {
            $sources = self::fetchSourceData();
            $list = $sources?->getRequestRelationList();
            if (!$list instanceof RequestRelationList) {
                return new RequestRelationList();
            }
            return $list;
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
            $entity = $result?->getEntity();
            if (!$entity instanceof Calendar) {
                return new Calendar();
            }
            return $entity;
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

            $collection = $result?->getCollection();
            if (!$collection instanceof ProcessList) {
                return new ProcessList();
            }

            return $collection;
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
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function submitClientData(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/";
            $result = \App::$http->readPostResult($url, $process);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function preconfirmProcess(Process $process): Process
    {
        try {
            $url = '/process/status/preconfirmed/';
            $result = \App::$http->readPostResult($url, $process);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function confirmProcess(Process $process): Process
    {
        try {
            $url = '/process/status/confirmed/';
            $result = \App::$http->readPostResult($url, $process);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function cancelAppointment(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/";
            $result = \App::$http->readDeleteResult($url, [], null);  // Changed to match test expectations
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function sendConfirmationEmail(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/confirmation/mail/";
            $result = \App::$http->readPostResult($url, $process);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function sendPreconfirmationEmail(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/preconfirmation/mail/";
            $result = \App::$http->readPostResult($url, $process);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function sendCancelationEmail(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/delete/mail/";
            $result = \App::$http->readPostResult($url, $process);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getProcessById(int $processId, string $authKey): Process
    {
        try {
            $resolveReferences = 2;
            $result = \App::$http->readGetResult("/process/{$processId}/{$authKey}/", [
                'resolveReferences' => $resolveReferences
            ]);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

    public static function getScopesByProviderId(string $source, string|int $providerId): ScopeList
    {
        try {
            $scopeList = self::getScopes();
            if (!$scopeList instanceof ScopeList) {
                return new ScopeList();
            }
            $result = $scopeList->withProviderID($source, (string) $providerId);
            if (!$result instanceof ScopeList) {
                return new ScopeList();
            }
            return $result;
        } catch (\Exception $e) {
            ExceptionService::handleException($e, __FUNCTION__);
        }
    }

}
