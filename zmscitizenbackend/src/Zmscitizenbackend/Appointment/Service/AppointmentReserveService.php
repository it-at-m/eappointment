<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Service;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentReserveRepository;
use BO\Zmscitizenbackend\Captcha\Service\CaptchaRequirementTrait;
use BO\Zmscitizenbackend\Captcha\Service\TokenValidationService;
use BO\Zmscitizenbackend\Core\Service\ValidationService;

class AppointmentReserveService
{
    use CaptchaRequirementTrait;

    private TokenValidationService $tokenValidator;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
    }

    public function processReservation(array $body, bool $showUnpublished = false): ThinnedProcess|array
    {
        $clientData = $this->extractClientData($body);

        $captchaRequired = $this->isCaptchaRequiredForOfficeId($clientData->officeId);
        $captchaToken = $body['captchaToken'] ?? null;

        $errors = ValidationService::validatePostAppointmentReserve(
            $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->timestamp,
            $captchaRequired,
            $captchaToken,
            $this->tokenValidator
        );
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $errors = ValidationService::validateServiceLocationCombination(
            $clientData->officeId,
            $clientData->serviceIds,
            $showUnpublished
        );
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return AppointmentReserveRepository::create()->reserveAppointment(
            (int) $clientData->officeId,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            (int) $clientData->timestamp
        );
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'officeId' => isset($body['officeId']) && is_numeric($body['officeId']) ? (int) $body['officeId'] : null,
            'serviceIds' => $body['serviceId'] ?? null,
            'serviceCounts' => $body['serviceCount'] ?? [1],
            'timestamp' => isset($body['timestamp']) && is_numeric($body['timestamp']) ? (int) $body['timestamp'] : null,
        ];
    }
}
