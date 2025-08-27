<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;

class AppointmentCancelService
{
    public function processCancel(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $process = AppointmentService::getThinnedProcessById($clientData->processId, $clientData->authKey, $authenticatedUser);

        if (is_array($process) && !empty($process['errors'])) {
            return $process;
        }

        if (!$this->canBeCancelled($process)) {
            return ['errors' => [
                [
                    'errorCode' => 'appointmentCanNotBeCanceled',
                    'statusCode' => 406
                ]
            ]];
        }

        if ($process->status !== 'reserved') {
            // Todo: check if the email template cancelled exists for the scope before submitting and sending
            $this->sendCancellationEmail($process);
        }

        return $this->cancelProcess($process);
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

    private function cancelProcess(ThinnedProcess $process): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        $result = ZmsApiFacadeService::cancelAppointment($processEntity);
        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }

    private function sendCancellationEmail(ThinnedProcess $process): void
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        ZmsApiFacadeService::sendCancellationEmail($processEntity);
    }
}
