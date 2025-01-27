<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Models\AvailableAppointmentsByOffice;
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
            'officeIds' => isset($queryParams['officeId'])
                ? array_map('trim', explode(',', (string) $queryParams['officeId']))
                : [],
            'serviceIds' => isset($queryParams['serviceId'])
                ? array_map('trim', explode(',', (string) $queryParams['serviceId']))
                : [],
            'serviceCounts' => isset($queryParams['serviceCount'])
                ? array_map('trim', explode(',', (string) $queryParams['serviceCount']))
                : []
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetAvailableAppointments(
            $data->date,
            $data->officeIds,
            $data->serviceIds,
            $data->serviceCounts
        );
    }

    private function getAvailableAppointments(
        object $data,
        ?bool $groupByOffice = false
    ): array|AvailableAppointments|AvailableAppointmentsByOffice {
        return ZmsApiFacadeService::getAvailableAppointments(
            $data->date,
            $data->officeIds,
            $data->serviceIds,
            $data->serviceCounts,
            $groupByOffice
        );
    }

    public function getAvailableAppointmentsListByOffice($queryParams): AvailableAppointmentsByOffice|array
    {
        $clientData = $this->extractClientData($queryParams);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getAvailableAppointments($clientData, true);
    }
}