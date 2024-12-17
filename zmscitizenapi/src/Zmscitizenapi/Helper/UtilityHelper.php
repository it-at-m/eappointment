<?php

namespace BO\Zmscitizenapi\Helper;

use \BO\Zmscitizenapi\Models\ThinnedProcess;
use \BO\Zmsentities\Appointment;
use \BO\Zmsentities\Client;
use \BO\Zmsentities\Contact;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Provider;
use \BO\Zmsentities\Request;
use \BO\Zmsentities\Scope;

class UtilityHelper
{

    private static function formatDateArray(\DateTime $date): array
    {
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }

    public static function getInternalDateFromISO($dateString): array
    {
        try {
            if (!is_string($dateString)) {
                throw new \InvalidArgumentException('Date string must be a string');
            }
            $date = new \DateTime($dateString);
            return self::formatDateArray($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid ISO date format: ' . $e->getMessage());
        }
    }

    public static function getInternalDateFromTimestamp(int $timestamp): array
    {
        try {
            $date = (new \DateTime())->setTimestamp($timestamp);
            return self::formatDateArray($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid timestamp: ' . $e->getMessage());
        }
    }

    public static function uniqueElementsFilter($value, $index, $self): bool
    {
        return array_search($value, $self) === $index;
    }

    public static function getClientIp(): string
    {
        $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP)) {
                return $_SERVER[$header];
            }
        }
        return '127.0.0.1';
    }

    public static function processToThinnedProcess(Process $myProcess): ThinnedProcess
    {
        if (!$myProcess || !isset($myProcess->id)) {
            return new ThinnedProcess();
        }

        $subRequestCounts = [];
        $mainServiceId = null;
        $mainServiceCount = 0;

        $requests = $myProcess->requests ?? [];
        if ($requests) {
            $requests = is_array($requests) ? $requests : iterator_to_array($requests);
            if (count($requests) > 0) {
                $mainServiceId = $requests[0]->id;
                foreach ($requests as $request) {
                    if ($request->id === $mainServiceId) {
                        $mainServiceCount++;
                    } else {
                        if (!isset($subRequestCounts[$request->id])) {
                            $subRequestCounts[$request->id] = [
                                'id' => $request->id,
                                'count' => 0,
                            ];
                        }
                        $subRequestCounts[$request->id]['count']++;
                    }
                }
            }
        }

        $thinnedProcess = new ThinnedProcess();
        $thinnedProcess->processId = $myProcess->id;
        $thinnedProcess->timestamp = isset($myProcess->appointments[0]) ? $myProcess->appointments[0]->date : null;
        $thinnedProcess->authKey = $myProcess->authKey ?? null;
        $thinnedProcess->familyName = isset($myProcess->clients[0]) ? $myProcess->clients[0]->familyName : null;
        $thinnedProcess->customTextfield = $myProcess->customTextfield ?? null;
        $thinnedProcess->email = isset($myProcess->clients[0]) ? $myProcess->clients[0]->email : null;
        $thinnedProcess->telephone = isset($myProcess->clients[0]) ? $myProcess->clients[0]->telephone : null;
        $thinnedProcess->officeName = $myProcess->scope->contact->name ?? null;
        $thinnedProcess->officeId = $myProcess->scope->provider->id ?? null;
        $thinnedProcess->scope = $myProcess->scope ?? null;
        $thinnedProcess->subRequestCounts = array_values($subRequestCounts);
        $thinnedProcess->serviceId = $mainServiceId;
        $thinnedProcess->serviceCount = $mainServiceCount;

        return $thinnedProcess;
    }

    public static function thinnedProcessToProcess(ThinnedProcess $thinnedProcess): Process
    {
        if (!$thinnedProcess || !isset($thinnedProcess->processId)) {
            return new Process();
        }

        $processEntity = new Process();
        $processEntity->id = $thinnedProcess->processId;
        $processEntity->authKey = $thinnedProcess->authKey ?? null;

        $client = new Client();
        $client->familyName = $thinnedProcess->familyName ?? null;
        $client->email = $thinnedProcess->email ?? null;
        $client->telephone = $thinnedProcess->telephone ?? null;
        $client->customTextfield = $thinnedProcess->customTextfield ?? null;

        $processEntity->clients = [$client];

        $thinnedProcess = new Appointment();
        $thinnedProcess->date = $thinnedProcess->timestamp ?? null;
        $processEntity->appointments = [$thinnedProcess];

        $scope = new Scope();
        if (isset($thinnedProcess->officeName)) {
            $scope->contact = new Contact();
            $scope->contact->name = $thinnedProcess->officeName;
        }
        if (isset($thinnedProcess->officeId)) {
            $scope->provider = new Provider();
            $scope->provider->id = $thinnedProcess->officeId;
            $scope->provider->source = \App::$source_name;
        }
        $processEntity->scope = $scope;

        $mainServiceId = $thinnedProcess->serviceId ?? null;
        $mainServiceCount = $thinnedProcess->serviceCount ?? 0;
        $subRequestCounts = $thinnedProcess->subRequestCounts ?? [];

        $requests = [];
        for ($i = 0; $i < $mainServiceCount; $i++) {
            $request = new Request();
            $request->id = $mainServiceId;
            $request->source = \App::$source_name;
            $requests[] = $request;
        }
        foreach ($subRequestCounts as $subRequest) {
            for ($i = 0; $i < ($subRequest['count'] ?? 0); $i++) {
                $request = new Request();
                $request->id = $subRequest['id'];
                $request->source = \App::$source_name;
                $requests[] = $request;
            }
        }
        $processEntity->requests = $requests;

        $processEntity->lastChange = time();
        $processEntity->createIP = self::getClientIp();
        $processEntity->createTimestamp = time();

        return $processEntity;
    }


}
