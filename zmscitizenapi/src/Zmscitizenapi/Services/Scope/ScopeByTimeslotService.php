<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Utils\DateTimeFormatHelper;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;

class ScopeByTimeslotService
{
    public function getScopeByTimeslot(array $queryParams): ThinnedScope|array
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getMatchingScope($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        $serviceIds = $queryParams['serviceId'] ?? [];
        if (!is_array($serviceIds)) {
            $serviceIds = array_filter(array_map('trim', explode(',', (string) $serviceIds)));
        }

        $serviceCounts = $queryParams['serviceCount'] ?? [];
        if (!is_array($serviceCounts)) {
            $serviceCounts = array_filter(array_map('trim', explode(',', (string) $serviceCounts)));
        }

        return (object) [
            'officeId' => isset($queryParams['officeId']) && is_numeric($queryParams['officeId'])
                ? (int) $queryParams['officeId']
                : null,
            'timestamp' => isset($queryParams['timestamp']) && is_numeric($queryParams['timestamp'])
                ? (int) $queryParams['timestamp']
                : null,
            'serviceIds' => array_values(array_map('strval', $serviceIds)),
            'serviceCounts' => array_values(array_map('intval', $serviceCounts)),
            'source' => isset($queryParams['source']) && $queryParams['source'] !== ''
                ? (string) $queryParams['source']
                : null,
        ];
    }

    private function validateClientData(object $clientData): array
    {
        if (empty($clientData->serviceCounts)) {
            $clientData->serviceCounts = array_fill(0, count($clientData->serviceIds), 1);
        }

        return ValidationService::validateGetScopeByTimeslot(
            $clientData->officeId,
            $clientData->timestamp,
            $clientData->serviceIds,
            $clientData->serviceCounts
        );
    }

    private function getMatchingScope(object $clientData): ThinnedScope|array
    {
        $process = $this->findMatchingProcess(
            $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->timestamp
        );

        if (!$process instanceof Process) {
            return [
                'errors' => [[
                    'errorCode' => 'scopesNotFound',
                    'errorMessage' => 'No scopes found.',
                    'statusCode' => 404,
                    'errorType' => 'error',
                ]]
            ];
        }

        $scopeId = $process->scope?->id ?? null;

        if (!$scopeId) {
            return [
                'errors' => [[
                    'errorCode' => 'scopeNotFound',
                    'errorMessage' => 'No scope id found on selected timeslot process.',
                    'statusCode' => 404,
                    'errorType' => 'error',
                ]]
            ];
        }

        $scope = ZmsApiFacadeService::getScopeById((int) $scopeId);

        if (is_array($scope) && isset($scope['errors'])) {
            return $scope;
        }

        if (!$scope instanceof ThinnedScope) {
            return [
                'errors' => [[
                    'errorCode' => 'scopeNotFound',
                    'errorMessage' => 'Scope could not be resolved.',
                    'statusCode' => 404,
                    'errorType' => 'error',
                ]]
            ];
        }

        if (
            $clientData->source !== null &&
            isset($scope->provider) &&
            isset($scope->provider->source) &&
            (string) $scope->provider->source !== (string) $clientData->source
        ) {
            return [
                'errors' => [[
                    'errorCode' => 'scopeNotFound',
                    'errorMessage' => 'Scope source does not match requested source.',
                    'statusCode' => 404,
                    'errorType' => 'error',
                ]]
            ];
        }

        return $scope;
    }

    private function findMatchingProcess(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        int $timestamp
    ): ?Process {
        $date = DateTimeFormatHelper::getInternalDateFromTimestamp($timestamp);

        $freeAppointments = ZmsApiFacadeService::getFreeAppointments(
            $officeId,
            $serviceIds,
            $serviceCounts,
            $date
        );

        if (!$freeAppointments instanceof ProcessList) {
            return null;
        }

        foreach ($freeAppointments as $process) {
            if (!isset($process->appointments) || empty($process->appointments)) {
                continue;
            }

            foreach ($process->appointments as $appointment) {
                if ((int) $appointment->date === $timestamp) {
                    return $process;
                }
            }
        }

        return null;
    }
}
