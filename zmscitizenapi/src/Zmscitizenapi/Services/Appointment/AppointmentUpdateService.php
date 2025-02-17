<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;

class AppointmentUpdateService
{
    public function processUpdate(array $body): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $reservedProcess = $this->getReservedProcess($clientData->processId, $clientData->authKey);
        if (is_array($reservedProcess) && !empty($reservedProcess['errors'])) {
            return $reservedProcess;
        }

        $updatedProcess = $this->updateProcessWithClientData($reservedProcess, $clientData);
        return $this->saveProcessUpdate($updatedProcess);
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'processId' => isset($body['processId']) && is_numeric($body['processId'])
                ? (int) $body['processId']
                : null,
            'authKey' => isset($body['authKey']) && is_string($body['authKey']) && trim($body['authKey']) !== ''
                ? htmlspecialchars(trim($body['authKey']), ENT_QUOTES, 'UTF-8')
                : null,
            'familyName' => isset($body['familyName']) && is_string($body['familyName']) ? (string) $body['familyName'] : null,
            'email' => isset($body['email']) && is_string($body['email']) ? (string) $body['email'] : null,
            'telephone' => isset($body['telephone']) && is_string($body['telephone']) ? (string) $body['telephone'] : null,
            'customTextfield' => isset($body['customTextfield']) && is_string($body['customTextfield']) ? (string) $body['customTextfield'] : null,
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateUpdateAppointmentInputs($data->processId, $data->authKey, $data->familyName, $data->email, $data->telephone, $data->customTextfield);
    }

    private function getReservedProcess(int $processId, string $authKey): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    }

    private function updateProcessWithClientData(ThinnedProcess $process, object $data): ThinnedProcess
    {
        $process->familyName = $data->familyName ?? $process->familyName ?? null;
        $process->email = $data->email ?? $process->email ?? null;
        $process->telephone = $data->telephone ?? $process->telephone ?? null;
        $process->customTextfield = $data->customTextfield ?? $process->customTextfield ?? null;
        return $process;
    }

    private function saveProcessUpdate(ThinnedProcess $process): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        $result = ZmsApiFacadeService::updateClientData($processEntity);
        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }
}
