<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Utils\DateTimeFormatHelper;
use BO\Zmscitizenapi\Utils\ErrorMessages;
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
            'serviceCounts' => array_values(array_map('strval', $serviceCounts)),
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

        $errors = ValidationService::validatePostAppointmentReserve(
            $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->timestamp
        );

        if (
            is_array($clientData->serviceIds) &&
            is_array($clientData->serviceCounts) &&
            count($clientData->serviceIds) !== count($clientData->serviceCounts)
        ) {
            $errors['errors'][] = ErrorMessages::get('mismatchedArrays');
        }

        if (empty($errors['errors'])) {
            $clientData->serviceCounts = array_values(array_map('intval', $clientData->serviceCounts));
        }

        return $errors;
    }

    private function getMatchingScope(object $clientData): ThinnedScope|array
    {
        $matchingProcesses = $this->findMatchingProcesses(
            $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->timestamp
        );

        if (is_array($matchingProcesses) && isset($matchingProcesses['errors'])) {
            return $matchingProcesses;
        }

        if ($matchingProcesses->count() === 0) {
            return [
                'errors' => [[
                    'errorCode' => 'scopesNotFound',
                    'errorMessage' => 'No scopes found.',
                    'statusCode' => 404,
                    'errorType' => 'error',
                ]]
            ];
        }

        foreach ($matchingProcesses as $process) {
            $scopeId = $process->scope?->id ?? null;

            if (!$scopeId) {
                continue;
            }

            $scope = ZmsApiFacadeService::getScopeById((int) $scopeId);

            if (is_array($scope) && isset($scope['errors'])) {
                $highestStatusCode = ErrorMessages::getHighestStatusCode($scope['errors']);

                if ($highestStatusCode >= 500) {
                    return $scope;
                }

                continue;
            }

            if (!$scope instanceof ThinnedScope) {
                continue;
            }

            if (
                $clientData->source !== null &&
                isset($scope->provider) &&
                isset($scope->provider->source) &&
                (string) $scope->provider->source !== (string) $clientData->source
            ) {
                continue;
            }

            return $scope;
        }

        return [
            'errors' => [[
                'errorCode' => 'scopeNotFound',
                'errorMessage' => 'Scope could not be resolved.',
                'statusCode' => 404,
                'errorType' => 'error',
            ]]
        ];
    }

    private function findMatchingProcesses(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        int $timestamp
    ): ProcessList|array {
        $date = DateTimeFormatHelper::getInternalDateFromTimestamp($timestamp);

        $freeAppointments = ZmsApiFacadeService::getFreeAppointments(
            $officeId,
            $serviceIds,
            $serviceCounts,
            $date
        );

        if (is_array($freeAppointments) && isset($freeAppointments['errors'])) {
            return $freeAppointments;
        }

        $matchingProcesses = new ProcessList();

        if (!$freeAppointments instanceof ProcessList) {
            return $matchingProcesses;
        }

        foreach ($freeAppointments as $process) {
            if (!isset($process->appointments) || empty($process->appointments)) {
                continue;
            }

            foreach ($process->appointments as $appointment) {
                if ((int) $appointment->date === $timestamp) {
                    $matchingProcesses->addEntity($process);
                    break;
                }
            }
        }

        return $matchingProcesses;
    }
}
