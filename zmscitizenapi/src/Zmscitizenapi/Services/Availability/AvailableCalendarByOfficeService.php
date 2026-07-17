<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableCalendarByOffice;
use BO\Zmscitizenapi\Services\Captcha\CaptchaRequirementTrait;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableCalendarByOfficeService
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

    public function getAvailableCalendarByOffice(
        array $queryParams,
        bool $showUnpublished = false
    ): AvailableCalendarByOffice|array {
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
            $clientData->endDate
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

        return (object) [
            'officeIds' => array_map('trim', explode(',', $queryParams['officeId'])),
            'serviceIds' => array_map('trim', explode(',', $queryParams['serviceId'])),
            'serviceCounts' => $serviceCounts,
            'startDate' => $queryParams['startDate'] ?? null,
            'endDate' => $queryParams['endDate'] ?? null,
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
            $this->tokenValidator
        );
    }
}
