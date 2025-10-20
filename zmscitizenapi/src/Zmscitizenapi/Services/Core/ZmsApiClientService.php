<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Utils\ClientIpHelper;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Process;
use BO\Zmsentities\Source;
use BO\Zmsentities\Collection\DayList;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Collection\ScopeList;

class ZmsApiClientService
{
    public static function getMergedMailTemplates(int $providerId): array
    {
        try {
            $cacheKey = 'merged_mailtemplates_' . $providerId;
            if (\App::$cache && ($cached = \App::$cache->get($cacheKey))) {
                return is_array($cached) ? $cached : [];
            }
            $result = \App::$http->readGetResult('/merged-mailtemplates/' . $providerId . '/');
            $templates = $result?->getCollection();
            if (!is_iterable($templates)) {
                return [];
            }
            $out = [];
            foreach ($templates as $template) {
                $name = is_array($template) ? ($template['name'] ?? null) : ($template->name ?? null);
                $value = is_array($template) ? ($template['value'] ?? null) : ($template->value ?? null);
                if ($name !== null && $value !== null) {
                    $out[(string)$name] = (string)$value;
                }
            }
            if (\App::$cache) {
                \App::$cache->set($cacheKey, $out, \App::$SOURCE_CACHE_TTL);
                LoggerService::logInfo('Cache set', [
                    'key' => $cacheKey,
                    'ttl' => \App::$SOURCE_CACHE_TTL,
                    'entity_type' => 'merged_mail_templates'
                ]);
            }
            return $out;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
        }
    }

    public static function getIcsContent(int $processId, string $authKey, string $status = 'appointment'): ?string
    {
        try {
            $url = "/process/{$processId}/{$authKey}/ics/";
            $result = \App::$http->readGetResult($url);
            $entity = $result?->getEntity();
            if ($entity instanceof \BO\Zmsentities\Ics) {
                return $entity->getContent() ?? null;
            }
            return null;
        } catch (\Exception $e) {
            // Do not fail the user flow if ICS is unavailable; just log and return null
            LoggerService::logError($e, null, null, [
                'processId' => $processId,
                'context' => 'ICS fetch via API'
            ]);
            return null;
        }
    }
    public static function getOffices(): ProviderList
    {
        try {
            $combined = new ProviderList();
            $seen = [];

            foreach (self::getSourceNames() as $name) {
                $src = self::fetchSourceDataFor($name);
                $list = $src?->getProviderList();

                if ($list instanceof ProviderList) {
                    foreach ($list as $provider) {
                        $key = (($provider->source ?? '') . '_' . $provider->id);
                        if (!isset($seen[$key])) {
                            $combined->addEntity($provider);
                            $seen[$key] = true;
                        }
                    }
                }
            }

            return $combined;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
        }
    }

    public static function getServices(): RequestList
    {
        try {
            $combined = new RequestList();
            $seen = [];

            foreach (self::getSourceNames() as $name) {
                $src = self::fetchSourceDataFor($name);
                $list = $src?->getRequestList();

                if ($list instanceof RequestList) {
                    foreach ($list as $request) {
                        $key = (($request->source ?? '') . '_' . $request->id);
                        if (!isset($seen[$key])) {
                            $combined->addEntity($request);
                            $seen[$key] = true;
                        }
                    }
                }
            }

            return $combined;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
        }
    }

    public static function getRequestRelationList(): RequestRelationList
    {
        try {
            $combined = new RequestRelationList();
            $seen = [];

            foreach (self::getSourceNames() as $name) {
                $src = self::fetchSourceDataFor($name);
                $list = $src?->getRequestRelationList();

                if ($list instanceof RequestRelationList) {
                    foreach ($list as $rel) {
                        $r = $rel->request ?? null;
                        $p = $rel->provider ?? null;

                        $key = (($r->source ?? '') . '_' . $r->id) . '|' . (($p->source ?? '') . '_' . $p->id);
                        if (!isset($seen[$key])) {
                            $combined->addEntity($rel);
                            $seen[$key] = true;
                        }
                    }
                }
            }

            return $combined;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
        }
    }

    public static function getScopes(): ScopeList
    {
        try {
            $combined = new ScopeList();
            $seen = [];

            foreach (self::getSourceNames() as $name) {
                $src = self::fetchSourceDataFor($name);
                $list = $src?->getScopeList();

                if ($list instanceof ScopeList) {
                    foreach ($list as $scope) {
                        $prov = $scope->getProvider();
                        $key = (($prov->source ?? '') . '_' . $prov->id);
                        if (!isset($seen[$key])) {
                            $combined->addEntity($scope);
                            $seen[$key] = true;
                        }
                    }
                }
            }

            return $combined;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
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
            $bookableDays = new DayList();
            foreach ($entity->days as $day) {
                if (isset($day['status']) && $day['status'] === 'bookable') {
                    $bookableDays->addEntity($day);
                }
            }
            $entity->days = $bookableDays;

            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
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
            $result = \App::$http->readPostResult('/process/status/free/unique/', $calendar);
            $collection = $result?->getCollection();
            if (!$collection instanceof ProcessList) {
                return new ProcessList();
            }

            return $collection;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
        }
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): Process
    {
        try {
            $requestList = self::getServices() ?? new RequestList();
            $requestSource = [];
            foreach ($requestList as $r) {
                $requestSource[(string)$r->id] = (string)($r->source ?? '');
            }

            $requests = [];
            foreach ($serviceIds as $index => $serviceId) {
                $sid = (string)$serviceId;
                $src = $requestSource[$sid] ?? null;
                if (!$src) {
                    return new Process();
                }
                $count = (int)($serviceCounts[$index] ?? 1);
                for ($i = 0; $i < $count; $i++) {
                    $requests[] = ['id' => $serviceId, 'source' => $src];
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
            return $entity instanceof Process ? $entity : new Process();
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
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
            ExceptionService::handleException($e);
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
            ExceptionService::handleException($e);
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
            ExceptionService::handleException($e);
        }
    }

    public static function cancelAppointment(Process $process): Process
    {
        try {
            $url = "/process/{$process->id}/{$process->authKey}/";
            $result = \App::$http->readDeleteResult($url, []);
            $entity = $result?->getEntity();
            if (!$entity instanceof Process) {
                return new Process();
            }
            return $entity;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
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
            ExceptionService::handleException($e);
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
            ExceptionService::handleException($e);
        }
    }

    public static function sendCancellationEmail(Process $process): Process
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
            ExceptionService::handleException($e);
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
            ExceptionService::handleException($e);
        }
    }

    public static function getScopesByProviderId(string $source, string|int $providerId): ScopeList
    {
        try {
            $scopeList = self::getScopes();
            if (!$scopeList instanceof ScopeList) {
                return new ScopeList();
            }
            $result = $scopeList->withProviderID($source, (string)$providerId);
            if (!$result instanceof ScopeList) {
                return new ScopeList();
            }
            return $result;
        } catch (\Exception $e) {
            ExceptionService::handleException($e);
        }
    }

    private static function fetchSourceDataFor(string $sourceName): Source
    {
        $cacheKey = 'source_' . $sourceName;
        if (\App::$cache && ($data = \App::$cache->get($cacheKey))) {
            return $data;
        }

        $result = \App::$http->readGetResult('/source/' . $sourceName . '/', [
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

    /**
     * Akzeptiert sowohl:
     * - String: "dldb", "dldb,zms", "dldb; zms", "dldb zms", "dldb|zms"
     * - Array:  ["dldb","zms"]
     */
    private static function getSourceNames(): array
    {
        $raw = \App::$source_name ?? 'dldb';

        if (is_array($raw)) {
            $names = array_values(array_filter(array_map('strval', $raw)));
        } else {
            $s = (string)$raw;
            $names = preg_split('/[,\;\|\s]+/', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $out = [];
        foreach ($names as $n) {
            $n = trim($n);
            if ($n !== '' && !in_array($n, $out, true)) {
                $out[] = $n;
            }
        }

        return $out ?: ['dldb'];
    }
}
