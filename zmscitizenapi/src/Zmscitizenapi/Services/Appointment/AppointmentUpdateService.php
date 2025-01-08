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

        $reservedProcess = $this->getReservedProcess(
            $clientData->processId,
            $clientData->authKey
        );

        if (is_array($reservedProcess) && !empty($reservedProcess['errors'])) {
            return $reservedProcess;
        }

        $updatedProcess = $this->updateProcessWithClientData($reservedProcess, $clientData);

        return $this->saveProcessUpdate($updatedProcess);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'processId' => isset($queryParams['processId']) && is_numeric($queryParams['processId'])
                ? (int) $queryParams['processId']
                : null,
            'authKey' => isset($queryParams['authKey']) && is_string($queryParams['authKey']) && trim($queryParams['authKey']) !== ''
                ? htmlspecialchars(trim($queryParams['authKey']), ENT_QUOTES, 'UTF-8')
                : null,
            'familyName' => isset($queryParams['familyName']) && is_string($queryParams['familyName']) ? (string) $queryParams['familyName'] : null,
            'email' => isset($queryParams['email']) && is_string($queryParams['email']) ? (string) $queryParams['email'] : null,
            'telephone' => isset($queryParams['telephone']) && is_string($queryParams['telephone']) ? (string) $queryParams['telephone'] : null,
            'customTextfield' => isset($queryParams['customTextfield']) && is_string($queryParams['customTextfield']) ? (string) $queryParams['customTextfield'] : null,
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateUpdateAppointmentInputs(
            $data->processId,
            $data->authKey,
            $data->familyName,
            $data->email,
            $data->telephone,
            $data->customTextfield
        );
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