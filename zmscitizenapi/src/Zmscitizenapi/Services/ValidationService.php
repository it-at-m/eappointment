<?php

namespace BO\Zmscitizenapi\Services;

class ValidationService
{

    public static function validateServiceLocationCombination($officeId, array $serviceIds)
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

    public static function validateGetBookableFreeDays($officeId, $serviceId, $startDate, $endDate, $serviceCounts)
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

    public static function validateGetProcessById($processId, $authKey)
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

    public static function validateGetAvailableAppointments($date, $officeId, $serviceIds, $serviceCounts)
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
    public static function validatePostAppointmentReserve($officeId, $serviceIds, $serviceCounts, $captchaSolution, $timestamp)
    {
        $errors = [];
        if (!$officeId) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Missing officeId.',
            ];
        } elseif (!is_numeric($officeId)) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Invalid officeId format. It should be a numeric value.',
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

        if (!$timestamp) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Missing timestamp.',
            ];
        } elseif (!is_numeric($timestamp) || $timestamp < 0) {
            $errors[] = [
                'status' => 400,
                'errorMessage' => 'Invalid timestamp format. It should be a positive numeric value.',
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

    public static function validateGetOfficesByServiceIds($serviceIds)
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

    public static function validateGetScopeByIds($scopeIds)
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

    public static function validateGetServicesByOfficeIds($officeIds)
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

    public static function validateGetProcessFreeSlots($freeSlots)
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

    public static function validateGetProcessByIdTimestamps($appointmentTimestamps)
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

    public static function validateGetProcessNotFound($process)
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

    public static function validateScopesNotFound($scopes)
    {
        $errors = [];
        if (empty($scopes)) {
            $errors[] = [
                'errorCode' => 'scopesNotFound',
                'errorMessage' => 'Scope(s) not found.',
                'status' => 404,
            ];
        }

        return ['errors' => $errors, 'status' => 404];
    }

    public static function validateServicesNotFound($services)
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

    public static function validateOfficesNotFound($offices)
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

    public static function validateAppointmentDaysNotFound($formattedDays)
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

    public static function validateNoAppointmentsAtLocation()
    {

        $errors[] = [
            'errorCode' => 'noAppointmentForThisScope',
            'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine.',
            'status' => 404,
        ];

        return ['errors' => $errors, 'status' => 404];

    }

    public static function validateUpdateAppointmentInputs($processId, $authKey, $familyName, $email, $telephone, $customTextfield)
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

        if ($customTextfield !== null && !is_string($customTextfield)) {
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


}

