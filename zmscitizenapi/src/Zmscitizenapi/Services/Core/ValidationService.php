<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ScopeList;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class ValidationService
{
    private const DATE_FORMAT = 'Y-m-d';
    private const MIN_PROCESS_ID = 1;
    private const PHONE_PATTERN = '/^\+?[1-9]\d{6,14}$/';
    private const SERVICE_COUNT_PATTERN = '/^\d+$/';
    private const MAX_FUTURE_DAYS = 365; // Maximum days in the future for appointments

    public static function validateServerGetRequest(?ServerRequestInterface $request): array
    {
        if (!$request instanceof ServerRequestInterface) {
            return ['errors' => [ErrorMessages::get('invalidRequest')]];
        }

        if ($request->getMethod() !== "GET") {
            return ['errors' => [ErrorMessages::get('invalidRequest')]];
        }
    
        return [];
    }

    public static function validateServerPostRequest(?ServerRequestInterface $request): array
    {
        if (!$request instanceof ServerRequestInterface) {
            return ['errors' => [ErrorMessages::get('invalidRequest')]];
        }

        if ($request->getMethod() !== "POST") {
            return ['errors' => [ErrorMessages::get('invalidRequest')]];
        }
    
        if ($request->getParsedBody() === null) {
            return ['errors' => [ErrorMessages::get('invalidRequest')]];
        }
    
        return [];
    }

    public static function validateServiceLocationCombination(int $officeId, array $serviceIds): array
    {
        if ($officeId <= 0) {
            return ['errors' => [ErrorMessages::get('invalidOfficeId')]];
        }

        if (empty($serviceIds) || !self::isValidNumericArray($serviceIds)) {
            return ['errors' => [ErrorMessages::get('invalidServiceId')]];
        }

        $availableServices = ZmsApiFacadeService::getServicesProvidedAtOffice($officeId);
        $availableServiceIds = [];
        foreach ($availableServices as $service) {
            $availableServiceIds[] = $service->id;
        }

        $invalidServiceIds = array_diff($serviceIds, $availableServiceIds);
        
        return empty($invalidServiceIds) 
            ? [] 
            : ['errors' => [ErrorMessages::get('invalidLocationAndServiceCombination')]];
    }

    public static function validateGetBookableFreeDays(
        ?int $officeId, 
        ?int $serviceId, 
        ?string $startDate, 
        ?string $endDate, 
        ?array $serviceCounts
    ): array {
        $errors = [];

        if (!self::isValidOfficeId($officeId)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }

        if (!self::isValidServiceId($serviceId)) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }

        if (!$startDate || !self::isValidDate($startDate)) {
            $errors[] = ErrorMessages::get('invalidStartDate');
        }

        if (!$endDate || !self::isValidDate($endDate)) {
            $errors[] = ErrorMessages::get('invalidEndDate');
        }

        if ($startDate && $endDate && self::isValidDate($startDate) && self::isValidDate($endDate)) {
            if (new DateTime($startDate) > new DateTime($endDate)) {
                $errors[] = ErrorMessages::get('startDateAfterEndDate');
            }

            if (!self::isDateRangeValid($startDate, $endDate)) {
                $errors[] = ErrorMessages::get('dateRangeTooLarge');
            }
        }

        if (!self::isValidServiceCounts($serviceCounts)) {
            $errors[] = ErrorMessages::get('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validateGetProcessById(?int $processId, ?string $authKey): array
    {
        $errors = [];

        if (!self::isValidProcessId($processId)) {
            $errors[] = ErrorMessages::get('invalidProcessId');
        }

        if (!self::isValidAuthKey($authKey)) {
            $errors[] = ErrorMessages::get('invalidAuthKey');
        }

        return ['errors' => $errors];
    }

    public static function validateGetAvailableAppointments(
        ?string $date, 
        ?int $officeId, 
        ?array $serviceIds, 
        ?array $serviceCounts
    ): array {
        $errors = [];

        if (!$date || !self::isValidDate($date)) {
            $errors[] = ErrorMessages::get('invalidDate');
        }

        if (!self::isValidOfficeId($officeId)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }

        if (!self::isValidServiceIds($serviceIds)) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }

        if (!self::isValidServiceCounts($serviceCounts)) {
            $errors[] = ErrorMessages::get('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validatePostAppointmentReserve(
        ?int $officeId, 
        ?array $serviceIds, 
        ?array $serviceCounts, 
        ?int $timestamp
    ): array {
        $errors = [];

        if (!self::isValidOfficeId($officeId)) {
            $errors[] = ErrorMessages::get('invalidOfficeId');
        }

        if (!self::isValidServiceIds($serviceIds)) {
            $errors[] = ErrorMessages::get('invalidServiceId');
        }

        if (!self::isValidTimestamp($timestamp)) {
            $errors[] = ErrorMessages::get('invalidTimestamp');
        }

        if (!self::isValidServiceCounts($serviceCounts)) {
            $errors[] = ErrorMessages::get('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validateUpdateAppointmentInputs(
        ?int $processId, 
        ?string $authKey, 
        ?string $familyName, 
        ?string $email, 
        ?string $telephone, 
        ?string $customTextfield
    ): array {
        $errors = [];

        if (!self::isValidProcessId($processId)) {
            $errors[] = ErrorMessages::get('invalidProcessId');
        }

        if (!self::isValidAuthKey($authKey)) {
            $errors[] = ErrorMessages::get('invalidAuthKey');
        }

        if (!self::isValidFamilyName($familyName)) {
            $errors[] = ErrorMessages::get('invalidFamilyName');
        }

        if (!self::isValidEmail($email)) {
            $errors[] = ErrorMessages::get('invalidEmail');
        }

        if (!self::isValidTelephone($telephone)) {
            $errors[] = ErrorMessages::get('invalidTelephone');
        }

        if (!self::isValidCustomTextfield($customTextfield)) {
            $errors[] = ErrorMessages::get('invalidCustomTextfield');
        }

        return ['errors' => $errors];
    }

    public static function validateGetScopeById(?int $scopeId): array
    {
        return !self::isValidScopeId($scopeId)
            ? ['errors' => [ErrorMessages::get('invalidScopeId')]]
            : [];
    }

    public static function validateGetServicesByOfficeId(?int $officeId): array
    {
        return !self::isValidOfficeId($officeId)
            ? ['errors' => [ErrorMessages::get('invalidOfficeId')]]
            : [];
    }

    public static function validateGetOfficeListByServiceId(?int $serviceId): array
    {
        return !self::isValidServiceId($serviceId)
            ? ['errors' => [ErrorMessages::get('invalidServiceId')]]
            : [];
    }

    public static function validateGetProcessFreeSlots(?ProcessList $freeSlots): array
    {
        return empty($freeSlots) || !is_iterable($freeSlots)
            ? ['errors' => [ErrorMessages::get('appointmentNotAvailable')]]
            : [];
    }

    public static function validateGetProcessByIdTimestamps(?array $appointmentTimestamps): array
    {
        return empty($appointmentTimestamps)
            ? ['errors' => [ErrorMessages::get('appointmentNotAvailable')]]
            : [];
    }

    public static function validateGetProcessNotFound(?Process $process): array
    {
        return !$process
            ? ['errors' => [ErrorMessages::get('appointmentNotAvailable')]]
            : [];
    }

    public static function validateScopesNotFound(?ScopeList $scopes): array
    {
        return empty($scopes) || $scopes === null || $scopes->count() === 0
            ? ['errors' => [ErrorMessages::get('scopesNotFound')]]
            : [];
    }

    public static function validateServicesNotFound(?array $services): array
    {
        return empty($services)
            ? ['errors' => [ErrorMessages::get('servicesNotFound')]]
            : [];
    }

    public static function validateOfficesNotFound(?array $offices): array
    {
        return empty($offices)
            ? ['errors' => [ErrorMessages::get('officesNotFound')]]
            : [];
    }

    public static function validateAppointmentDaysNotFound(?array $formattedDays): array
    {
        return empty($formattedDays)
            ? ['errors' => [ErrorMessages::get('noAppointmentForThisDay')]]
            : [];
    }

    public static function validateNoAppointmentsAtLocation(): array
    {
        return ['errors' => [ErrorMessages::get('noAppointmentsAtLocation')]];
    }

    /*  Helper methods for validation */
    private static function isValidDate(string $date): bool
    {
        $dateTime = DateTime::createFromFormat(self::DATE_FORMAT, $date);
        return $dateTime && $dateTime->format(self::DATE_FORMAT) === $date;
    }

    private static function isDateRangeValid(string $startDate, string $endDate): bool
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        
        return $diff->days <= self::MAX_FUTURE_DAYS;
    }

    private static function isValidNumericArray(array $array): bool
    {
        return !empty($array) && array_filter($array, 'is_numeric') === $array;
    }

    private static function isValidOfficeId(?int $officeId): bool
    {
        return !empty($officeId) && $officeId > 0;
    }

    private static function isValidServiceId(?int $serviceId): bool
    {
        return !empty($serviceId) && $serviceId > 0;
    }

    private static function isValidScopeId(?int $scopeId): bool
    {
        return !empty($scopeId) && $scopeId > 0;
    }

    private static function isValidProcessId(?int $processId): bool
    {
        return !empty($processId) && $processId >= self::MIN_PROCESS_ID;
    }

    private static function isValidAuthKey(?string $authKey): bool
    {
        return !empty($authKey) && is_string($authKey) && strlen(trim($authKey)) > 0;
    }

    private static function isValidServiceIds(?array $serviceIds): bool
    {
        return !empty($serviceIds) && self::isValidNumericArray($serviceIds);
    }

    private static function isValidServiceCounts(?array $serviceCounts): bool
    {
        if (empty($serviceCounts) || !is_array($serviceCounts)) {
            return false;
        }

        foreach ($serviceCounts as $count) {
            if (!is_numeric($count) || $count < 0 || !preg_match(self::SERVICE_COUNT_PATTERN, (string)$count)) {
                return false;
            }
        }

        return true;
    }

    private static function isValidTimestamp(?int $timestamp): bool
    {
        return !empty($timestamp) && is_numeric($timestamp) && $timestamp > time();
    }

    private static function isValidEmail(?string $email): bool
    {
        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private static function isValidTelephone(?string $telephone): bool
    {
        return $telephone === null || preg_match(self::PHONE_PATTERN, $telephone);
    }

    private static function isValidFamilyName(?string $familyName): bool
    {
        return !empty($familyName) && is_string($familyName) && strlen(trim($familyName)) > 0;
    }

    private static function isValidCustomTextfield(?string $customTextfield): bool
    {
        return $customTextfield === null || (is_string($customTextfield) && strlen(trim($customTextfield)) > 0);
    }
}