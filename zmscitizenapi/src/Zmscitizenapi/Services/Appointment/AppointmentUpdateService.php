<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;
use BO\Zmsentities\Helper\ProcessPlainText;

class AppointmentUpdateService
{
    public function processUpdate(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $validated = $this->validateClientData($clientData, $authenticatedUser);
        if (!$validated instanceof ThinnedProcess) {
            return $validated;
        }

        $updatedProcess = $this->updateProcessWithClientData(
            $validated,
            $clientData,
            $this->isRebookingUpdate($clientData)
        );
        return $this->saveProcessUpdate($updatedProcess, $authenticatedUser);
    }

    /**
     * @return ThinnedProcess|array{errors: array}
     */
    private function validateClientData(object $data, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $authErrors = ValidationService::validateGetProcessById($data->processId, $data->authKey);
        if ($authErrors['errors'] !== []) {
            return ['errors' => $authErrors['errors']];
        }

        $reservedProcess = $this->getReservedProcess($data->processId, $data->authKey, $authenticatedUser);
        if (!$reservedProcess instanceof ThinnedProcess) {
            $errors = array_key_exists('errors', $reservedProcess) && is_array($reservedProcess['errors'])
                ? $reservedProcess['errors']
                : [];
            return ['errors' => $errors];
        }

        if ($this->isRebookingUpdate($data)) {
            $lockErrors = ValidationService::validateUnchangedStoredContact(
                $reservedProcess,
                $data->familyName,
                $data->email,
                $data->telephone,
                $data->customTextfield,
                $data->customTextfield2
            );
            if ($lockErrors['errors'] !== []) {
                return ['errors' => $lockErrors['errors']];
            }
        }

        $fieldErrors = ValidationService::validateAppointmentUpdateFields(
            $data->familyName,
            $data->email,
            $data->telephone,
            $data->customTextfield,
            $data->customTextfield2,
            $reservedProcess->scope ?? null
        );
        if ($fieldErrors['errors'] !== []) {
            return ['errors' => $fieldErrors['errors']];
        }

        return $reservedProcess;
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
            'familyName' => isset($body['familyName']) && is_string($body['familyName']) ? $body['familyName'] : null,
            'email' => isset($body['email']) && is_string($body['email']) ? $body['email'] : null,
            'telephone' => isset($body['telephone']) && is_string($body['telephone']) ? $body['telephone'] : null,
            'customTextfield' => isset($body['customTextfield']) && is_string($body['customTextfield']) ? $body['customTextfield'] : null,
            'customTextfield2' => isset($body['customTextfield2']) && is_string($body['customTextfield2']) ? $body['customTextfield2'] : null,
            'sourceProcessId' => isset($body['sourceProcessId']) && is_numeric($body['sourceProcessId'])
                ? (int) $body['sourceProcessId']
                : null,
            'sourceAuthKey' => isset($body['sourceAuthKey']) && is_string($body['sourceAuthKey'])
                && trim($body['sourceAuthKey']) !== ''
                ? htmlspecialchars(trim($body['sourceAuthKey']), ENT_QUOTES, 'UTF-8')
                : null,
        ];
    }

    private function isRebookingUpdate(object $data): bool
    {
        return ($data->sourceProcessId ?? null) !== null
            && ($data->sourceAuthKey ?? null) !== null;
    }

    private function getReservedProcess(int $processId, ?string $authKey, ?AuthenticatedUser $user): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey, $user);
    }

    private function updateProcessWithClientData(
        ThinnedProcess $process,
        object $data,
        bool $lockStoredContact = false
    ): ThinnedProcess {
        if (!$lockStoredContact || !ValidationService::isFilledContactValue($process->familyName)) {
            $process->familyName = $data->familyName ?? $process->familyName ?? null;
        }
        if (!$lockStoredContact || !ValidationService::isFilledEmail($process->email)) {
            $process->email = $data->email ?? $process->email ?? null;
        }
        if (!$lockStoredContact || !ValidationService::isFilledContactValue($process->telephone)) {
            $process->telephone = $data->telephone ?? $process->telephone ?? null;
        }
        if (
            $data->customTextfield !== null
            && (!$lockStoredContact || !ValidationService::isFilledContactValue($process->customTextfield))
        ) {
            $process->customTextfield = ProcessPlainText::normalize($data->customTextfield);
        }
        if (
            $data->customTextfield2 !== null
            && (!$lockStoredContact || !ValidationService::isFilledContactValue($process->customTextfield2))
        ) {
            $process->customTextfield2 = ProcessPlainText::normalize($data->customTextfield2);
        }
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
