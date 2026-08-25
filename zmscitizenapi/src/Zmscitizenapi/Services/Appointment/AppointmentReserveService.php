<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Utils\DateTimeFormatHelper;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Captcha\CaptchaRequirementTrait;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\MapperService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmsentities\Process;

class AppointmentReserveService
{
    use CaptchaRequirementTrait;

    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function processReservation(array $body, bool $showUnpublished = false): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $captchaRequired = $this->isCaptchaRequiredForOfficeId($clientData->officeId);
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
            $clientData->serviceIds,
            $showUnpublished
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

        $sourceProcess = $this->loadSourceProcessForRebooking($clientData);
        if (is_array($sourceProcess)) {
            return $sourceProcess;
        }

        return $this->reserveAppointment(
            $selectedProcess,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->officeId,
            $sourceProcess
        );
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'officeId' => isset($body['officeId']) && is_numeric($body['officeId']) ? (int) $body['officeId'] : null,
            'serviceIds' => $body['serviceId'] ?? null,
            'serviceCounts' => $body['serviceCount'] ?? [1],
            'timestamp' => isset($body['timestamp']) && is_numeric($body['timestamp']) ? (int) $body['timestamp'] : null,
            'sourceProcessId' => isset($body['sourceProcessId']) && is_numeric($body['sourceProcessId'])
                ? (int) $body['sourceProcessId']
                : null,
            'sourceAuthKey' => isset($body['sourceAuthKey']) && is_string($body['sourceAuthKey'])
                && trim($body['sourceAuthKey']) !== ''
                ? htmlspecialchars(trim($body['sourceAuthKey']), ENT_QUOTES, 'UTF-8')
                : null,
        ];
    }

    /**
     * @return ThinnedProcess|array{errors: array}|null
     */
    private function loadSourceProcessForRebooking(object $clientData): ThinnedProcess|array|null
    {
        if ($clientData->sourceProcessId === null || $clientData->sourceAuthKey === null) {
            return null;
        }
        $sourceProcess = ZmsApiFacadeService::getThinnedProcessById(
            $clientData->sourceProcessId,
            $clientData->sourceAuthKey,
            null
        );
        return $sourceProcess;
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

    private function reserveAppointment(
        Process $process,
        array $serviceIds,
        array $serviceCounts,
        int $officeId,
        ?ThinnedProcess $sourceProcess
    ): ThinnedProcess|array {
        $process->clients = [
            [
                'email' => 'test@muenchen.de'
            ]
        ];
        $reservedProcess = ZmsApiFacadeService::reserveTimeslot($process, $serviceIds, $serviceCounts);
        if (is_array($reservedProcess)) {
            return $reservedProcess;
        }
        if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
            $scopeId = $reservedProcess->scope->id;
            $scope = ZmsApiFacadeService::getScopeById((int) $scopeId);
            if (!is_array($scope)) {
                $reservedProcess->scope = $scope;
                $reservedProcess->officeId = $officeId;
            }
        }

        if ($sourceProcess instanceof ThinnedProcess && $reservedProcess instanceof ThinnedProcess) {
            return $this->copySourceContactOntoReservedProcess($reservedProcess, $sourceProcess);
        }

        return $reservedProcess;
    }

    private function copySourceContactOntoReservedProcess(
        ThinnedProcess $reservedProcess,
        ThinnedProcess $sourceProcess
    ): ThinnedProcess|array {
        $reservedProcess->familyName = $sourceProcess->familyName;
        if (ValidationService::isFilledContactValue($sourceProcess->email)) {
            $reservedProcess->email = $sourceProcess->email;
        }
        $reservedProcess->telephone = $sourceProcess->telephone;
        $reservedProcess->customTextfield = $sourceProcess->customTextfield;
        $reservedProcess->customTextfield2 = $sourceProcess->customTextfield2;

        $updatedProcess = ZmsApiFacadeService::updateClientData(
            MapperService::thinnedProcessToProcess($reservedProcess)
        );
        if (is_array($updatedProcess)) {
            return $updatedProcess;
        }

        $copiedProcess = MapperService::processToThinnedProcess($updatedProcess);
        $copiedProcess->scope = $reservedProcess->scope;
        $copiedProcess->officeId = $reservedProcess->officeId;
        return $copiedProcess;
    }
}
