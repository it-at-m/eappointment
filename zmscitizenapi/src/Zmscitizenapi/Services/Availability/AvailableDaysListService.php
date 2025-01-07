<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableDaysListService
{
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
        return (object) [
            'officeId' => isset($queryParams['officeId']) ? (int) $queryParams['officeId'] : null,
            'serviceId' => isset($queryParams['serviceId']) ? (int) $queryParams['serviceId'] : null,
            'serviceCounts' => isset($queryParams['serviceCount'])
                ? array_map('trim', explode(',', $queryParams['serviceCount']))
                : [],
            'startDate' => isset($queryParams['startDate']) ? (string) $queryParams['startDate'] : null,
            'endDate' => isset($queryParams['endDate']) ? (string) $queryParams['endDate'] : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetBookableFreeDays(
            $data->officeId,
            $data->serviceId,
            $data->startDate,
            $data->endDate,
            $data->serviceCounts
        );
    }

    private function getAvailableDays(object $data): array|AvailableDays
    {
        return ZmsApiFacadeService::getBookableFreeDays(
            $data->officeId,
            $data->serviceId,
            $data->serviceCounts,
            $data->startDate,
            $data->endDate
        );
    }
}

