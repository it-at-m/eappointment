<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Models\AvailableDaysByOffice;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableDaysListService
{
    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function getAvailableDaysList(array $queryParams): AvailableDays|array
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getAvailableDays($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        $queryParams['officeId'] = isset($queryParams['officeId']) ? (string) $queryParams['officeId'] : '';
        $queryParams['serviceId'] = isset($queryParams['serviceId']) ? (string) $queryParams['serviceId'] : '';
        $serviceCount = $queryParams['serviceCount'] ?? '';
        $serviceCounts = !empty($serviceCount)
            ? array_map('trim', explode(',', (string) $serviceCount))
            : [];
        return (object) [
            'officeIds' => array_map('trim', explode(',', $queryParams['officeId'])),
            'serviceIds' => array_map('trim', explode(',', $queryParams['serviceId'])),
            'serviceCounts' => $serviceCounts,
            'startDate' => $queryParams['startDate'] ?? null,
            'endDate' => $queryParams['endDate'] ?? null
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
        $captchaToken = $queryParams['captchaToken'] ?? null;

        return ValidationService::validateGetBookableFreeDays(
            $data->officeIds,
            $data->serviceIds,
            $data->startDate,
            $data->endDate,
            $data->serviceCounts,
            $captchaRequired,
            $captchaToken,
            $this->tokenValidator
        );
    }

    private function getAvailableDays(object $data, ?bool $groupByOffice = false): AvailableDays|AvailableDaysByOffice|array
    {
        return ZmsApiFacadeService::getBookableFreeDays(
            $data->officeIds,
            $data->serviceIds,
            $data->serviceCounts,
            $data->startDate,
            $data->endDate,
            $groupByOffice
        );
    }

    public function getAvailableDaysListByOffice($queryParams)
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getAvailableDays($clientData, true);
    }
}
