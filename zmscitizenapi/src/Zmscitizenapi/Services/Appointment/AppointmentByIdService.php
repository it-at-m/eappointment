<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Captcha\CaptchaService;
use BO\Zmscitizenapi\Services\Core\ValidationService;

class AppointmentByIdService
{
    private CaptchaService $captchaService;

    public function __construct()
    {
        $this->captchaService = new CaptchaService();
    }

    public function getAppointmentById(array $queryParams, ?AuthenticatedUser $authenticatedUser): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $appointment = AppointmentService::getThinnedProcessById($clientData->processId, $clientData->authKey, $authenticatedUser);

        $token = $this->captchaService->generateToken();
        $appointment->setCaptchaToken($token);

        return $appointment;
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
        return ValidationService::validateGetProcessById($data->processId, $data->authKey);
    }

}
