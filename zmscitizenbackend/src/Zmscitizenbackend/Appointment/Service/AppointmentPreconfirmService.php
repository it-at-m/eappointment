<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Service;

use BO\Zmscitizenbackend\Core\Model\AuthenticatedUser;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentPreconfirmRepository;
use BO\Zmscitizenbackend\Mail\Repository\MailQueueRepository;
use BO\Zmscitizenbackend\Core\Service\ValidationService;

class AppointmentPreconfirmService
{
    public function processPreconfirm(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $reservedProcess = AppointmentByIdRepository::create()->readAppointmentById(
            (int) $clientData->processId,
            $clientData->authKey,
            $authenticatedUser
        );

        $result = AppointmentPreconfirmRepository::create()->preconfirmAppointment($reservedProcess);

        if ($result->status === 'preconfirmed') {
            $this->sendPreconfirmationEmail($result);
        }

        return $result;
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'processId' => isset($body['processId']) && is_numeric($body['processId'])
                ? (int) $body['processId']
                : null,
            'authKey' => isset($body['authKey']) && is_string($body['authKey']) && trim($body['authKey']) !== ''
                ? htmlspecialchars(trim($body['authKey']), ENT_QUOTES, 'UTF-8')
                : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetProcessById($data->processId, $data->authKey);
    }

    private function sendPreconfirmationEmail(ThinnedProcess $process): void
    {
        MailQueueRepository::create()->queuePreconfirmationMail($process);
    }
}
