<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Appointment;

use BO\Zmscitizenbackend\Models\AuthenticatedUser;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\AppointmentByIdRepository;
use BO\Zmscitizenbackend\Repository\AppointmentConfirmRepository;
use BO\Zmscitizenbackend\Repository\MailQueueRepository;
use BO\Zmscitizenbackend\Services\Captcha\CaptchaService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;

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

        $reservedProcess = AppointmentByIdRepository::create()->readAppointmentById(
            (int) $clientData->processId,
            $clientData->authKey,
            $authenticatedUser
        );

        $result = AppointmentConfirmRepository::create()->confirmAppointment($reservedProcess);

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
                : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetProcessById($data->processId, $data->authKey);
    }

    private function sendConfirmationEmail(ThinnedProcess $process): void
    {
        MailQueueRepository::create()->queueConfirmationMail($process);
    }
}
