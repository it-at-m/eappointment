<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services;

use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class ValidationService
{

    public static function validateServiceLocationCombination(int $officeId, array $serviceIds): array
    {

        $availableServices = ZmsApiFacadeService::getServicesProvidedAtOffice($officeId);

        $availableServiceIds = [];
        foreach ($availableServices as $service) {
            $availableServiceIds[] = $service->id;
        }
        
        $invalidServiceIds = array_filter($serviceIds, function ($serviceId) use ($availableServiceIds) {
            return !in_array($serviceId, $availableServiceIds);
        });

        $errors = [];
        if (!empty($invalidServiceIds)) {
            $errors[] = ErrorMessages::get('invalidLocationAndServiceCombination');
        }
        return ['errors' => $errors];
    }

    public static function validateGetBookableFreeDays(?int $officeId, ?int $serviceId, ?string $startDate, ?string $endDate, ?array $serviceCounts): array
    {
        $errors = [];
        if (!$startDate) {
            $errors[] = ErrorMessages::get('invalidStartDate');
        } elseif (!\DateTime::createFromFormat('Y-m-d', $startDate)) {
            $errors[] = ErrorMessages::get('invalidStartDateFormat');
        }
        if (!$endDate) {
            $errors[] = ErrorMessages::get('invalidEndDate');
        } elseif (!\DateTime::createFromFormat('Y-m-d', $endDate)) {
            $errors[] = ErrorMessages::get('invalidEndDateFormat');
        }
        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }
        if (!$serviceId || !is_numeric($serviceId)) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }
        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceCounts))) {
            $errors[] = ErrorMessages::get('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validateGetProcessById(?int $processId, ?string $authKey): array
    {
        $errors = [];
        if (!$processId || !is_numeric($processId) || intval($processId) <= 0) {
            $errors[] = ErrorMessages::get('invalidProcessId');
        }

        if (!$authKey || !is_string($authKey)) {
            $errors[] = ErrorMessages::get('invalidAuthKey');
        }

        return ['errors' => $errors];
    }

    public static function validateGetAvailableAppointments(?string $date, ?int $officeId, ?array $serviceIds, ?array $serviceCounts): array
    {
        $errors = [];
        if (!$date) {
            $errors[] = ErrorMessages::get('invalidDate');
        }

        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }

        if (empty($serviceIds) || !is_array($serviceIds) || array_filter($serviceIds, fn($id) => !is_numeric($id))) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }

        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceCounts))) {
            $errors[] = ErrorMessages::get('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validatePostAppointmentReserve(?int $officeId, ?array $serviceIds, ?array $serviceCounts, ?int $timestamp): array
    {
        $errors = [];
        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }

        if (empty($serviceIds)) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        } elseif (!is_array($serviceIds) || array_filter($serviceIds, fn($id) => !is_numeric($id))) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }

        if (!$timestamp || !is_numeric($timestamp) || $timestamp < 0) {
            $errors[] = ErrorMessages::get('invalidTimestamp');
        }

        if (!is_array($serviceCounts) || array_filter($serviceCounts, fn($count) => !is_numeric($count) || $count < 0)) {
            $errors[] = ErrorMessages::get('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validateGetOfficesByServiceIds(?array $serviceIds): array
    {
        $errors = [];
        if (empty($serviceIds) || $serviceIds == ['']) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }

        return ['errors' => $errors];
    }

    public static function validateGetScopeByIds(?int $scopeId): array
    {
        $errors = [];
        if (empty($scopeId) || $scopeId === '') {
            $errors[] = ErrorMessages::get('invalidScopeId');
        }

        return ['errors' => $errors];
    }

    public static function validateGetServicesByOfficeIds(?array $officeIds): array
    {

        $errors = [];

        if (empty($officeIds) || !is_array($officeIds)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }

        foreach ($officeIds as $id) {
            if (!is_numeric($id)) {
                $errors[] = ErrorMessages::get('invalidOfficeId');
            }
        }

        return ['errors' => $errors];
    }

    public static function validateGetProcessFreeSlots(?ProcessList $freeSlots): array
    {
        $errors = [];
        if (empty($freeSlots) || !is_iterable($freeSlots)) {
            $errors[] = ErrorMessages::get('appointmentNotAvailable');
        }

        return ['errors' => $errors];
    }

    public static function validateGetProcessByIdTimestamps(?array $appointmentTimestamps): array
    {
        $errors = [];
        if (empty($appointmentTimestamps)) {
            $errors[] = ErrorMessages::get('appointmentNotAvailable');
        }

        return ['errors' => $errors];
    }

    public static function validateGetProcessNotFound(?Process $process): array
    {
        $errors = [];
        if (!$process) {
            $errors[] = ErrorMessages::get('appointmentNotAvailable');
        }

        return ['errors' => $errors];
    }

    public static function validateScopesNotFound(?ScopeList $scopes): array
    {
        $errors = [];
        if (empty($scopes) || $scopes === null || $scopes->count() === 0) {
            $errors[] = ErrorMessages::get('scopesNotFound');
        }
        return ['errors' => $errors];
    }

    public static function validateServicesNotFound(?array $services): array
    {
        $errors = [];
        if (empty($services)) {
            $errors[] = ErrorMessages::get('servicesNotFound');
        }

        return ['errors' => $errors];
    }

    public static function validateOfficesNotFound(?array $offices): array
    {
        $errors = [];
        if (empty($offices)) {
            $errors[] = ErrorMessages::get('officesNotFound');
        }

        return ['errors' => $errors];
    }

    public static function validateAppointmentDaysNotFound(?array $formattedDays): array
    {
        $errors = [];
        if (empty($formattedDays)) {
            $errors[] = ErrorMessages::get('noAppointmentForThisDay');
        }

        return ['errors' => $errors];
    }

    public static function validateNoAppointmentsAtLocation(): array
    {

        $errors = [];
        $errors[] = ErrorMessages::get('noAppointmentsAtLocation');

        return ['errors' => $errors];

    }

    public static function validateUpdateAppointmentInputs(?int $processId, ?string $authKey, ?string $familyName, ?string $email, ?string $telephone, ?string $customTextfield): array
    {
        $errors = [];

        if (!$processId || !is_numeric($processId) || intval($processId) <= 0) {
            $errors[] = ErrorMessages::get('invalidProcessId');
        }

        if (!$authKey || !is_string($authKey)) {
            $errors[] = ErrorMessages::get('invalidAuthKey');
        }

        if (!$familyName || !is_string($familyName)) {
            $errors[] = ErrorMessages::get('invalidFamilyName');
        }

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = ErrorMessages::get('invalidEmail');
        }

        if ($telephone !== null && (!$telephone || !preg_match('/^\d{7,15}$/', $telephone))) {
            $errors[] = ErrorMessages::get('invalidTelephone');
        }

        if ($customTextfield !== null && (!is_string($customTextfield) || is_numeric($customTextfield))) {
            $errors[] = ErrorMessages::get('invalidCustomTextfield');
        }

        return ['errors' => $errors];
    }

    public static function validateServiceIdParam(array $serviceIds): array
    {
        $errors = [];
        
        if (empty($serviceIds) || !is_array($serviceIds)) {
            $errors[] = ErrorMessages::get('invalidServiceId');

            return ['errors' => $errors];
        }

        foreach ($serviceIds as $id) {
            if (!is_numeric($id)) {
                $errors[] = ErrorMessages::get('invalidServiceId');
            }
        }

        return empty($errors) ? [] : ['errors' => $errors];
    }

    public static function validateScopeIdParam(?int $scopeId): array
    {
        $errors = [];

        if (empty($scopeId) || !is_numeric($scopeId)) {
            $errors[] = ErrorMessages::get('invalidScopeId');

            return ['errors' => $errors];
        }

        return empty($errors) ? [] : ['errors' => $errors];
    }

}
