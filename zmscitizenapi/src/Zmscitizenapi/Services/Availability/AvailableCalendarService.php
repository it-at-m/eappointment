<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableCalendar;
use BO\Zmscitizenapi\Services\Captcha\CaptchaRequirementTrait;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableCalendarService
{
    use CaptchaRequirementTrait;
    use ServiceLocationValidationTrait;

    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function getAvailableCalendar(
        array $queryParams,
        bool $showUnpublished = false
    ): AvailableCalendar|array {
        $clientData = $this->extractClientData($queryParams);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $errors = $this->validateServiceLocations($clientData->officeIds, $clientData->serviceIds, $showUnpublished);
        if ($errors !== null) {
            return $errors;
        }

        return ZmsApiFacadeService::getCalendarAvailability(
            $clientData->officeIds,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->startDate,
            $clientData->endDate,
            $clientData->slotsStartDate,
            $clientData->slotsEndDate
        );
    }

    private function extractClientData(array $queryParams): object
    {
        $queryParams['officeId'] = isset($queryParams['officeId']) ? (string) $queryParams['officeId'] : '';
        $queryParams['serviceId'] = isset($queryParams['serviceId']) ? (string) $queryParams['serviceId'] : '';
        $serviceCount = $queryParams['serviceCount'] ?? '';
        $serviceCounts = !empty($serviceCount)
            ? array_map('trim', explode(',', (string) $serviceCount))
            : [];

        $slotsStartDate = isset($queryParams['slotsStartDate']) && $queryParams['slotsStartDate'] !== ''
            ? (string) $queryParams['slotsStartDate']
            : null;
        $slotsEndDate = isset($queryParams['slotsEndDate']) && $queryParams['slotsEndDate'] !== ''
            ? (string) $queryParams['slotsEndDate']
            : null;

        return (object) [
            'officeIds' => array_map('trim', explode(',', $queryParams['officeId'])),
            'serviceIds' => array_map('trim', explode(',', $queryParams['serviceId'])),
            'serviceCounts' => $serviceCounts,
            'startDate' => $queryParams['startDate'] ?? null,
            'endDate' => $queryParams['endDate'] ?? null,
            'slotsStartDate' => $slotsStartDate,
            'slotsEndDate' => $slotsEndDate,
            'captchaToken' => isset($queryParams['captchaToken']) ? (string) $queryParams['captchaToken'] : null,
        ];
    }

    private function validateClientData(object $data): array
    {
        $captchaRequired = $this->isCaptchaRequiredForOfficeIds($data->officeIds);

        return ValidationService::validateGetBookableFreeDays(
            $data->officeIds,
            $data->serviceIds,
            $data->startDate,
            $data->endDate,
            $data->serviceCounts,
            $captchaRequired,
            $data->captchaToken,
            $this->tokenValidator,
            $data->slotsStartDate,
            $data->slotsEndDate
        );
    }
}
