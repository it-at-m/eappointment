<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Service;

use BO\Zmscitizenbackend\Availability\Model\AvailableCalendar;
use BO\Zmscitizenbackend\Availability\Repository\AvailableCalendarRepository;
use BO\Zmscitizenbackend\Captcha\Service\CaptchaRequirementTrait;
use BO\Zmscitizenbackend\Captcha\Service\TokenValidationService;
use BO\Zmscitizenbackend\Core\Service\ValidationService;

class AvailableCalendarService
{
    use CaptchaRequirementTrait;
    use ServiceLocationValidationTrait;

    private TokenValidationService $tokenValidator;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
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

        return AvailableCalendarRepository::create()->readAvailableCalendar(
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
        $queryParams['officeIds'] = isset($queryParams['officeIds']) ? (string) $queryParams['officeIds'] : '';
        $queryParams['serviceIds'] = isset($queryParams['serviceIds']) ? (string) $queryParams['serviceIds'] : '';
        $serviceCountsRaw = $queryParams['serviceCounts'] ?? '';
        $serviceCounts = !empty($serviceCountsRaw)
            ? array_map('trim', explode(',', (string) $serviceCountsRaw))
            : [];

        $slotsStartDate = isset($queryParams['slotsStartDate']) && $queryParams['slotsStartDate'] !== ''
            ? (string) $queryParams['slotsStartDate']
            : null;
        $slotsEndDate = isset($queryParams['slotsEndDate']) && $queryParams['slotsEndDate'] !== ''
            ? (string) $queryParams['slotsEndDate']
            : null;

        return (object) [
            'officeIds' => array_map('trim', explode(',', $queryParams['officeIds'])),
            'serviceIds' => array_map('trim', explode(',', $queryParams['serviceIds'])),
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
