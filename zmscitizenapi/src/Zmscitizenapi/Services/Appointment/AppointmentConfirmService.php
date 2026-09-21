<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Captcha\CaptchaService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Core\MapperService;

class AppointmentConfirmService
{
    private CaptchaService $captchaService;

    public function __construct()
    {
        $this->captchaService = new CaptchaService();
    }

    public function processConfirm(array $body, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $sourceProcess = $this->loadSourceProcessForRebooking($clientData);
        if (is_array($sourceProcess) && !empty($sourceProcess['errors'])) {
            return $sourceProcess;
        }

        $reservedProcess = $this->getReservedProcess(
            $clientData->processId,
            $clientData->authKey,
            $authenticatedUser,
            $sourceProcess instanceof ThinnedProcess ? $sourceProcess : null
        );
        if (is_array($reservedProcess) && !empty($reservedProcess['errors'])) {
            return $reservedProcess;
        }

        // Todo: check if the email template confirmed exists for the scope before submitting and sending
        $result = $this->confirmProcess($reservedProcess);
        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        $token = $this->captchaService->generateToken();
        $result->setCaptchaToken($token);

        if ($result->status === 'confirmed') {
            $this->sendConfirmationEmail($result);
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
                : null,
            'sourceProcessId' => isset($body['sourceProcessId']) && is_numeric($body['sourceProcessId'])
                ? (int) $body['sourceProcessId']
                : null,
            'sourceAuthKey' => isset($body['sourceAuthKey']) && is_string($body['sourceAuthKey'])
                && trim($body['sourceAuthKey']) !== ''
                ? htmlspecialchars(trim($body['sourceAuthKey']), ENT_QUOTES, 'UTF-8')
                : null,
        ];
    }

    private function isRebookingConfirm(object $data): bool
    {
        return ($data->sourceProcessId ?? null) !== null
            && ($data->sourceAuthKey ?? null) !== null;
    }

    private function validateClientData(object $data): array
    {
        $errors = ValidationService::validateGetProcessById($data->processId, $data->authKey);
        if ($errors['errors'] !== [] || !$this->isRebookingConfirm($data)) {
            return $errors;
        }

        return ValidationService::validateGetProcessById($data->sourceProcessId, $data->sourceAuthKey);
    }

    /**
     * @return ThinnedProcess|array{errors: array}|null
     */
    private function loadSourceProcessForRebooking(object $clientData): ThinnedProcess|array|null
    {
        if (!$this->isRebookingConfirm($clientData)) {
            return null;
        }

        return ZmsApiFacadeService::getThinnedProcessById(
            $clientData->sourceProcessId,
            $clientData->sourceAuthKey,
            null
        );
    }

    private function getReservedProcess(
        int $processId,
        ?string $authKey,
        ?AuthenticatedUser $user,
        ?ThinnedProcess $sourceProcess
    ): ThinnedProcess|array {
        $process = ZmsApiFacadeService::getProcessById($processId, $authKey, $user);
        $notFound = ValidationService::validateGetProcessNotFound($process);
        if (!empty($notFound['errors'])) {
            return $notFound;
        }

        $thinned = MapperService::processToThinnedProcess($process);
        $confirmErrors = ValidationService::validateAppointmentConfirm(
            $thinned,
            $process->getExternalUserId(),
            $sourceProcess
        );
        if ($confirmErrors['errors'] !== []) {
            return $confirmErrors;
        }

        return $thinned;
    }

    private function confirmProcess(ThinnedProcess $process): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        $result = ZmsApiFacadeService::confirmAppointment($processEntity);
        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }

    private function sendConfirmationEmail(ThinnedProcess $process): void
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        ZmsApiFacadeService::sendConfirmationEmail($processEntity);
    }
}
