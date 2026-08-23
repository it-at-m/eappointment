<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Service;

use BO\Zmscitizenbackend\Core\Model\AuthenticatedUser;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentUpdateRepository;
use BO\Zmscitizenbackend\Core\Service\ValidationService;
use BO\Zmscitizenbackend\Mail\Helper\ProcessPlainText;

class AppointmentUpdateService
{
    public function processUpdate(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $authErrors = ValidationService::validateGetProcessById($clientData->processId, $clientData->authKey);
        if ($authErrors['errors'] !== []) {
            return ['errors' => $authErrors['errors']];
        }

        $reservedProcess = AppointmentByIdRepository::create()->readAppointmentById(
            (int) $clientData->processId,
            $clientData->authKey,
            $authenticatedUser
        );

        $fieldErrors = ValidationService::validateAppointmentUpdateFields(
            $clientData->familyName,
            $clientData->email,
            $clientData->telephone,
            $clientData->customTextfield,
            $clientData->customTextfield2,
            $reservedProcess->scope ?? null
        );
        if ($fieldErrors['errors'] !== []) {
            return ['errors' => $fieldErrors['errors']];
        }

        $updatedProcess = $this->updateProcessWithClientData($reservedProcess, $clientData);
        return AppointmentUpdateRepository::create()->updateClientData($updatedProcess, $authenticatedUser);
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
            'customTextfield' => isset($body['customTextfield']) && is_string($body['customTextfield'])
                ? $body['customTextfield']
                : null,
            'customTextfield2' => isset($body['customTextfield2']) && is_string($body['customTextfield2'])
                ? $body['customTextfield2']
                : null,
        ];
    }

    private function updateProcessWithClientData(ThinnedProcess $process, object $data): ThinnedProcess
    {
        $process->familyName = $data->familyName ?? $process->familyName ?? null;
        $process->email = $data->email ?? $process->email ?? null;
        $process->telephone = $data->telephone ?? $process->telephone ?? null;
        if ($data->customTextfield !== null) {
            $process->customTextfield = ProcessPlainText::normalize($data->customTextfield);
        }
        if ($data->customTextfield2 !== null) {
            $process->customTextfield2 = ProcessPlainText::normalize($data->customTextfield2);
        }
        return $process;
    }
}
