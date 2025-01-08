<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;

class AppointmentCancelService
{
    public function processCancel(array $body): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $process = $this->getProcess(
            $clientData->processId,
            $clientData->authKey
        );

        if (is_array($process) && !empty($process['errors'])) {
            return $process;
        }

        if (!$this->canBeCancelled($process)) {
            return ['errors' => [ErrorMessages::get('appointmentCanNotBeCanceled')]];
        }

        // Send cancellation email before cancelling the appointment
        $this->sendCancellationEmail($process);

        return $this->cancelProcess($process);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'processId' => isset($queryParams['processId']) && is_numeric($queryParams['processId'])
                ? (int) $queryParams['processId']
                : null,
            'authKey' => isset($queryParams['authKey']) && is_string($queryParams['authKey']) && trim($queryParams['authKey']) !== ''
                ? htmlspecialchars(trim($queryParams['authKey']), ENT_QUOTES, 'UTF-8')
                : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetProcessById(
            $data->processId,
            $data->authKey
        );
    }

    private function getProcess(int $processId, string $authKey): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    }

    private function canBeCancelled(ThinnedProcess $process): bool
    {
        $appointmentTime = new \DateTimeImmutable("@{$process->timestamp}");
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'));
        return $appointmentTime > $now;
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
        ZmsApiFacadeService::sendCancelationEmail($processEntity);
    }
}