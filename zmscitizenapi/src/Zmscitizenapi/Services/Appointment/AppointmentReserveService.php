<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmsentities\Process;

class AppointmentReserveService
{
    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function processReservation(array $body): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $captchaRequired = $this->isCaptchaRequired($clientData->officeId);
        $captchaToken = $body['captchaToken'] ?? null;

        $errors = ValidationService::validatePostAppointmentReserve(
            $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->timestamp,
            $captchaRequired,
            $captchaToken,
            $this->tokenValidator
        );
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $errors = ValidationService::validateServiceLocationCombination(
            $clientData->officeId,
            $clientData->serviceIds
        );
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $selectedProcess = $this->findMatchingProcess(
            $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->timestamp
        );

        $errors = ValidationService::validateGetProcessNotFound($selectedProcess);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->reserveAppointment(
            $selectedProcess,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->officeId
        );
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'officeId' => isset($body['officeId']) && is_numeric($body['officeId']) ? (int) $body['officeId'] : null,
            'serviceIds' => $body['serviceId'] ?? null,
            'serviceCounts' => $body['serviceCount'] ?? [1],
            'timestamp' => isset($body['timestamp']) && is_numeric($body['timestamp']) ? (int) $body['timestamp'] : null,
        ];
    }

    private function isCaptchaRequired(?int $officeId): bool
    {
        try {
            $scope = $this->zmsApiFacadeService->getScopeByOfficeId((int) $officeId);
            return $scope->captchaActivatedRequired ?? false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function findMatchingProcess(int $officeId, array $serviceIds, array $serviceCounts, int $timestamp): ?Process
    {
        $freeAppointments = ZmsApiFacadeService::getFreeAppointments($officeId, $serviceIds, $serviceCounts, DateTimeFormatHelper::getInternalDateFromTimestamp($timestamp));
        foreach ($freeAppointments as $process) {
            if (!isset($process->appointments) || empty($process->appointments)) {
                continue;
            }

            foreach ($process->appointments as $appointment) {
                if ((int) $appointment->date === $timestamp) {
                    $requestIds = [];
                    if ($process->requests) {
                        foreach ($process->requests as $request) {
                            $requestIds[] = $request->getId();
                        }
                    }

                    $processData = [
                        'requests' => $requestIds,
                        'appointments' => [$appointment]
                    ];
                    $process->withUpdatedData($processData, new \DateTime("@$timestamp"), $process->scope);
                    return $process;
                }
            }
        }

        return null;
    }

    private function reserveAppointment(Process $process, array $serviceIds, array $serviceCounts, int $officeId): ThinnedProcess
    {
        $process->clients = [
            [
                'email' => 'test@muenchen.de'
            ]
        ];
        $reservedProcess = ZmsApiFacadeService::reserveTimeslot($process, $serviceIds, $serviceCounts);
        if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
            $scopeId = $reservedProcess->scope->id;
            $scope = ZmsApiFacadeService::getScopeById((int) $scopeId);
            if (!isset($scope['errors']) && isset($scope) && !empty($scope)) {
                $reservedProcess->scope = $scope;
                $reservedProcess->officeId = $officeId;
            }
        }

        return $reservedProcess;
    }
}
