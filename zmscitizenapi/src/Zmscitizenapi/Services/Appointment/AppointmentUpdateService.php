<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;

class AppointmentUpdateService
{
    public function processUpdate(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $errors = $this->validateClientData($clientData, $authenticatedUser);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $reservedProcess = $this->getReservedProcess($clientData->processId, $clientData->authKey, $authenticatedUser);

        $updatedProcess = $this->updateProcessWithClientData($reservedProcess, $clientData);
        return $this->saveProcessUpdate($updatedProcess, $authenticatedUser);
    }

    private function validateClientData(object $data, ?AuthenticatedUser $authenticatedUser): array
    {
        $authErrors = ValidationService::validateGetProcessById($data->processId, $data->authKey);
        if (is_array($authErrors) && !empty($authErrors['errors'])) {
            return $authErrors;
        }

        $reservedProcess = $this->getReservedProcess($data->processId, $data->authKey, $authenticatedUser);
        if (is_array($reservedProcess) && !empty($reservedProcess['errors'])) {
            return $reservedProcess;
        }

        $fieldErrors = ValidationService::validateAppointmentUpdateFields(
            $data->familyName,
            $data->email,
            $data->telephone,
            $data->customTextfield,
            $data->customTextfield2,
            $reservedProcess->scope ?? null
        );
        if (is_array($fieldErrors) && !empty($fieldErrors['errors'])) {
            return $fieldErrors;
        }

        return ['errors' => []];
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
            'customTextfield2' => isset($body['customTextfield2']) && is_string($body['customTextfield2']) ? (string) $body['customTextfield2'] : null,
        ];
    }

    private function getReservedProcess(int $processId, string $authKey, ?AuthenticatedUser $user): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey, $user);
    }

    private function updateProcessWithClientData(ThinnedProcess $process, object $data): ThinnedProcess
    {
        $process->familyName = $data->familyName ?? $process->familyName ?? null;
        $process->email = $data->email ?? $process->email ?? null;
        $process->telephone = $data->telephone ?? $process->telephone ?? null;
        $process->customTextfield = $data->customTextfield ?? $process->customTextfield ?? null;
        $process->customTextfield2 = $data->customTextfield2 ?? $process->customTextfield2 ?? null;
        return $process;
    }

    private function saveProcessUpdate(ThinnedProcess $process, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        if (!is_null($authenticatedUser) && is_null($processEntity->getExternalUserId())) {
            $processEntity->setExternalUserId($authenticatedUser->getExternalUserId());
        }
        $result = ZmsApiFacadeService::updateClientData($processEntity);
        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }
}
