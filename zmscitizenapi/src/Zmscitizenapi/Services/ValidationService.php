<?php

namespace BO\Zmscitizenapi\Services;

use \BO\Zmsentities\Process;
use \BO\Zmsentities\Collection\ProcessList;
use \BO\Zmsentities\Collection\ScopeList;

class ValidationService
{

    public static function validateServiceLocationCombination(int $officeId, array $serviceIds): array
    {
        $availableServices = ZmsApiFacadeService::getServicesProvidedAtOffice($officeId);
        $availableServiceIds = array_map(function ($service) {
            return $service['id'];
        }, $availableServices);

        $invalidServiceIds = array_filter($serviceIds, function ($serviceId) use ($availableServiceIds) {
            return !in_array($serviceId, $availableServiceIds);
        });

        if (!empty($invalidServiceIds)) {
            return [
                'status' => 400,
                'errorCode' => 'invalidLocationAndServiceCombination',
                'errorMessage' => 'The provided service(s) do not exist at the given location.',
                'invalidServiceIds' => $invalidServiceIds,
                'locationId' => $officeId
            ];
        }

        return [
            'status' => 200,
            'message' => 'Valid service-location combination.',
        ];
    }

    public static function validateGetBookableFreeDays(?int $officeId, ?int $serviceId, ?string $startDate, ?string $endDate, ?array $serviceCounts): array
    {
        $errors = [];
        if (!$startDate) {
            $errors[] = ['status' => 400, 'errorMessage' => 'startDate is required and must be a valid date.'];
        }
        if (!$endDate) {
            $errors[] = ['status' => 400, 'errorMessage' => 'endDate is required and must be a valid date.'];
        }
        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = ['status' => 400, 'errorMessage' => 'officeId should be a 32-bit integer.'];
        }
        if (!$serviceId || !is_numeric($serviceId)) {
            $errors[] = ['status' => 400, 'errorMessage' => 'serviceId should be a 32-bit integer.'];
        }
        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceCounts))) {
            $errors[] = ['status' => 400, 'errorMessage' => 'serviceCount should be a comma-separated string of integers.'];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validateGetProcessById(?int $processId, ?string $authKey): array
    {
        $errors = [];
        if (!$processId || !is_numeric($processId) || intval($processId) <= 0) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'processId should be a 32-bit integer.',
            ];
        }

        if (!$authKey || !is_string($authKey)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'authKey should be a string.',
            ];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validateGetAvailableAppointments(?string $date, ?int $officeId, ?array $serviceIds, ?array $serviceCounts): array
    {
        $errors = [];
        if (!$date) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'date is required and must be a valid date.',
            ];
        }

        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'officeId should be a 32-bit integer.',
            ];
        }

        if (empty($serviceIds[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceIds))) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'serviceId should be a comma-separated string of integers.',
            ];
        }

        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceCounts))) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'serviceCount should be a comma-separated string of integers.',
            ];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validatePostAppointmentReserve(?int $officeId, ?array $serviceIds, ?array $serviceCounts, ?int $timestamp): array
    {
        $errors = [];
        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'officeId should be a 32-bit integer.',
            ];
        }

        if (empty($serviceIds)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Missing serviceId.',
            ];
        } elseif (!is_array($serviceIds) || array_filter($serviceIds, fn($id) => !is_numeric($id))) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Invalid serviceId format. It should be an array of numeric values.',
            ];
        }

        if (!$timestamp || !is_numeric($timestamp) || $timestamp < 0) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Missing timestamp or invalid timestamp format. It should be a positive numeric value.',
            ];
        }

        if (!is_array($serviceCounts) || array_filter($serviceCounts, fn($count) => !is_numeric($count) || $count < 0)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Invalid serviceCount format. It should be an array of non-negative numeric values.',
            ];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validateGetOfficesByServiceIds(?array $serviceIds): array
    {
        $errors = [];
        if (empty($serviceIds) || $serviceIds == ['']) {
            $errors[] = [
                'offices' => [],
                'errorMessage' => 'Invalid serviceId(s).',
                'status' => 400
            ];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validateGetScopeByIds(?array $scopeIds): array
    {
        $errors = [];
        if (empty($scopeIds) || $scopeIds == ['']) {
            $errors[] = [
                'scopes' => [],
                'errorMessage' => 'Invalid scopeId(s).',
                'status' => 400
            ];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validateGetServicesByOfficeIds(?array $officeIds): array
    {

        $errors = [];
        if (empty($officeIds) || $officeIds == ['']) {
            $errors[] = [
                'services' => [],
                'errorMessage' => 'Invalid officeId(s)',
                'status' => 400,
            ];
        }

        return ['errors' => $errors, 'status' => 400];
    }

    public static function validateGetProcessFreeSlots(?ProcessList $freeSlots): array
    {
        $errors = [];
        if (empty($freeSlots) || !is_iterable($freeSlots)) {
            $errors[] = [
                'appointmentTimestamps' => [],
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateGetProcessByIdTimestamps(?array $appointmentTimestamps): array
    {
        $errors = [];
        if (empty($appointmentTimestamps)) {
            $errors[] = [
                'appointmentTimestamps' => [],
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateGetProcessNotFound(?Process $process): array
    {
        $errors = [];
        if (!$process) {
            $errors[] = [
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateScopesNotFound(?ScopeList $scopes): array
    {
        $errors = [];
        if (empty($scopes) || $scopes === null || $scopes->count() === 0) {
            $errors[] = [
                'errorCode' => 'scopesNotFound',
                'errorMessage' => 'Scope(s) not found.',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateServicesNotFound(?array $services): array 
    {
        $errors = [];
        if (empty($services)) {
            $errors[] = [
                'errorCode' => 'servicesNotFound',
                'errorMessage' => 'Service(s) not found for the provided officeId(s).',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateOfficesNotFound(?array $offices): array
    {
        $errors = [];
        if (empty($offices)) {
            $errors[] = [
                'errorCode' => 'officesNotFound',
                'errorMessage' => 'Office(s) not found for the provided serviceId(s).',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateAppointmentDaysNotFound(?array $formattedDays): array
    {
        $errors = [];
        if (empty($formattedDays)) {
            $errors[] = [
                'errorCode' => 'noAppointmentForThisDay',
                'errorMessage' => 'No available days found for the given criteria.',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateNoAppointmentsAtLocation(): array
    {

        $errors[] = [
            'errorCode' => 'noAppointmentForThisScope',
            'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine.',
            'status' => 404,
        ];

        return ['errors' => $errors, 'status' => 404];

    }

    public static function validateUpdateAppointmentInputs(?int $processId, ?string $authKey, ?string $familyName, ?string $email, ?string $telephone, ?string $customTextfield): array
    {
        $errors = [];

        if (!$processId || !is_numeric($processId) || intval($processId) <= 0) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'processId should be a positive 32-bit integer.',
            ];
        }

        if (!$authKey || !is_string($authKey)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'authKey should be a non-empty string.',
            ];
        }

        if (!$familyName || !is_string($familyName)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'familyName should be a non-empty string.',
            ];
        }

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'email should be a valid email address.',
            ];
        }

        if ($telephone !== null && !$telephone || !preg_match('/^\d{7,15}$/', $telephone)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.',
            ];
        }

        if ($customTextfield !== null && (!is_string($customTextfield) || is_numeric($customTextfield))) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'customTextfield should be a string.',
            ];
        }

        if (!empty($errors)) {
            return ['errors' => $errors, 'status' => 400];
        }

        return ['status' => 200, 'message' => 'Valid input for updating appointment.'];
    }
    
    public static function validateServiceIdParam(array $serviceIds): array
    {
        $errors = [];

        foreach ($serviceIds as $id) {
            if (!is_numeric($id)) {
                $errors[] = [
                    'offices' => [],
                    'errorMessage' => "Invalid service ID: $id. Must be a number.",
                    'status' => 400,
                ];
            }
        }

        return empty($errors) ? [] : $errors;
    }   

    public static function validateScopeIdParam(array $serviceIds): array
    {
        $errors = [];

        foreach ($serviceIds as $id) {
            if (!is_numeric($id)) {
                $errors[] = [
                    'scopes' => [],
                    'errorMessage' => "Invalid scope ID: $id. Must be a number.",
                    'status' => 400,
                ];
            }
        }

        return empty($errors) ? [] : $errors;
    }

}

