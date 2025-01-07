<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;

class AppointmentPreconfirmService
{
    public function processPreconfirm(array $body): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $reservedProcess = $this->getReservedProcess(
            $clientData->processId,
            $clientData->authKey
        );

        if (is_array($reservedProcess) && !empty($reservedProcess['errors'])) {
            return $reservedProcess;
        }

        $result = $this->preconfirmProcess($reservedProcess);
        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        if ($result->status === 'preconfirmed') {
            $this->sendPreconfirmationEmail($result);
        }

        return $result;
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'processId' => isset($body['processId']) ? (int) $body['processId'] : 0,
            'authKey' => isset($body['authKey']) ? (string) $body['authKey'] : null,
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetProcessById(
            $data->processId,
            $data->authKey
        );
    }

    private function getReservedProcess(int $processId, string $authKey): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    }

    private function preconfirmProcess(ThinnedProcess $process): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        $result = ZmsApiFacadeService::preconfirmAppointment($processEntity);

        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }

    private function sendPreconfirmationEmail(ThinnedProcess $process): void
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        ZmsApiFacadeService::sendPreconfirmationEmail($processEntity);
    }
}