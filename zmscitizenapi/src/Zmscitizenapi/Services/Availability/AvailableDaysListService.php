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

        $result = $this->getAvailableDays($clientData);
        $errors = ValidationService::validateAppointmentDaysNotFound($result->toArray());

        if (!empty($errors)) {
            return $errors;
        }

        return $result;
    }

    private function extractClientData(array $queryParams): object
    {
        $serviceCount = $queryParams['serviceCount'] ?? '';
        $serviceCounts = !empty($serviceCount)
            ? array_map('trim', explode(',', $serviceCount))
            : [];

        return (object) [
            'officeIds' => array_map('trim', explode(',', $queryParams['officeId'] ?? '')),
            'serviceIds' => array_map('trim', explode(',', $queryParams['serviceId'] ?? '')),
            'serviceCounts' => $serviceCounts,
            'startDate' => $queryParams['startDate'] ?? null,
            'endDate' => $queryParams['endDate'] ?? null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetBookableFreeDays(
            $data->officeIds,
            $data->serviceIds,
            $data->startDate,
            $data->endDate,
            $data->serviceCounts
        );
    }

    private function getAvailableDays(object $data): AvailableDays
    {
        return ZmsApiFacadeService::getBookableFreeDays(
            $data->officeIds,
            $data->serviceIds,
            $data->serviceCounts,
            $data->startDate,
            $data->endDate
        );
    }
}

