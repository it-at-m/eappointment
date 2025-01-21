<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableAppointmentsListService
{
    public function getAvailableAppointmentsList(array $queryParams): AvailableAppointments|array
    {
        $clientData = $this->extractClientData($queryParams);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getAvailableAppointments($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'date' => isset($queryParams['date']) ? (string) $queryParams['date'] : null,
            'officeId' => isset($queryParams['officeId']) ? (int) $queryParams['officeId'] : null,
            'serviceIds' => isset($queryParams['serviceId'])
                ? array_map('trim', explode(',', $queryParams['serviceId']))
                : [],
            'serviceCounts' => isset($queryParams['serviceCount'])
                ? array_map('trim', explode(',', $queryParams['serviceCount']))
                : []
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetAvailableAppointments(
            $data->date,
            $data->officeId,
            $data->serviceIds,
            $data->serviceCounts
        );
    }

    private function getAvailableAppointments(object $data): array|AvailableAppointments
    {
        return ZmsApiFacadeService::getAvailableAppointments(
            $data->date,
            $data->officeId,
            $data->serviceIds,
            $data->serviceCounts
        );
    }
}