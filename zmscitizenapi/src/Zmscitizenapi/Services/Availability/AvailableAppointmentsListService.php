<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Models\AvailableAppointmentsByOffice;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableAppointmentsListService
{
    use ServiceLocationValidationTrait;

    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function getAvailableAppointmentsList(array $queryParams): AvailableAppointments|array
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $errors = $this->validateServiceLocations($clientData->officeIds, $clientData->serviceIds);
        if ($errors !== null) {
            return $errors;
        }

        return $this->getAvailableAppointments($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'date' => isset($queryParams['date']) ? (string) $queryParams['date'] : null,
            'officeIds' => isset($queryParams['officeId'])
                ? array_map('trim', explode(',', (string) $queryParams['officeId']))
                : [],
            'serviceIds' => isset($queryParams['serviceId'])
                ? array_map('trim', explode(',', (string) $queryParams['serviceId']))
                : [],
            'serviceCounts' => isset($queryParams['serviceCount'])
                ? array_map('trim', explode(',', (string) $queryParams['serviceCount']))
                : [],
            'captchaToken' => isset($queryParams['captchaToken']) ? (string) $queryParams['captchaToken'] : null
        ];
    }

    private function isCaptchaRequired(array $officeIds): bool
    {
        $officeId = (int)($officeIds[0] ?? 0);

        try {
            $scope = $this->zmsApiFacadeService->getScopeByOfficeId($officeId);
            return $scope->captchaActivatedRequired ?? false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function validateClientData(object $data): array
    {
        $captchaRequired = $this->isCaptchaRequired($data->officeIds);
        $captchaToken = $data->captchaToken;

        return ValidationService::validateGetAvailableAppointments(
            $data->date,
            $data->officeIds,
            $data->serviceIds,
            $data->serviceCounts,
            $captchaRequired,
            $captchaToken,
            $this->tokenValidator
        );
    }

    private function getAvailableAppointments(object $data, ?bool $groupByOffice = false): array|AvailableAppointments|AvailableAppointmentsByOffice
    {
        return ZmsApiFacadeService::getAvailableAppointments(
            $data->date,
            $data->officeIds,
            $data->serviceIds,
            $data->serviceCounts,
            $groupByOffice
        );
    }

    public function getAvailableAppointmentsListByOffice($queryParams): AvailableAppointments|AvailableAppointmentsByOffice|array
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $errors = $this->validateServiceLocations($clientData->officeIds, $clientData->serviceIds);
        if ($errors !== null) {
            return $errors;
        }

        return $this->getAvailableAppointments($clientData, true);
    }
}
