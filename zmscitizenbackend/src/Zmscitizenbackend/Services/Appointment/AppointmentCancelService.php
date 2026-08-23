<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Appointment;

use BO\Zmscitizenbackend\Models\AuthenticatedUser;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentCancelRepository;
use BO\Zmscitizenbackend\Repository\Mail\MailQueueRepository;
use BO\Zmscitizenbackend\Services\Core\ValidationService;

class AppointmentCancelService
{
    public function processCancel(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $process = AppointmentByIdRepository::create()->readAppointmentById(
            (int) $clientData->processId,
            $clientData->authKey,
            $authenticatedUser
        );

        if (!$this->canBeCancelled($process)) {
            return ['errors' => [
                [
                    'errorCode' => 'appointmentCanNotBeCanceled',
                    'statusCode' => 406
                ]
            ]];
        }

        if ($process->status !== 'reserved') {
            $this->sendCancellationEmail($process);
        }

        return AppointmentCancelRepository::create()->cancelAppointment($process);
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

    private function canBeCancelled(ThinnedProcess $process): bool
    {
        $appointmentTime = new \DateTimeImmutable("@{$process->timestamp}");
        return $appointmentTime > \App::$now;
    }

    private function sendCancellationEmail(ThinnedProcess $process): void
    {
        MailQueueRepository::create()->queueCancellationMail($process);
    }
}
