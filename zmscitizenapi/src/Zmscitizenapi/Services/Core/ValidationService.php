<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\LanguageMiddleware;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ScopeList;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class ValidationService
{
    private static ?string $currentLanguage = null;
    private const DATE_FORMAT = 'Y-m-d';
    private const MIN_PROCESS_ID = 1;
    private const PHONE_PATTERN = '/^\+?[1-9]\d{6,14}$/';
    private const SERVICE_COUNT_PATTERN = '/^\d+$/';
    private const MAX_FUTURE_DAYS = 365;
// Maximum days in the future for appointments

    public static function setLanguageContext(?string $language): void
    {
        self::$currentLanguage = $language;
    }

    private static function getError(string $key): array
    {
        return ErrorMessages::get($key, self::$currentLanguage);
    }

    public static function validateServerGetRequest(?ServerRequestInterface $request): array
    {
        if (!$request instanceof ServerRequestInterface) {
            return ['errors' => [self::getError('invalidRequest')]];
        }

        if ($request->getMethod() !== "GET") {
            return ['errors' => [self::getError('invalidRequest')]];
        }

        return [];
    }

    public static function validateServerPostRequest(?ServerRequestInterface $request): array
    {
        if (!$request instanceof ServerRequestInterface) {
            return ['errors' => [self::getError('invalidRequest')]];
        }

        if ($request->getMethod() !== "POST") {
            return ['errors' => [self::getError('invalidRequest')]];
        }

        if ($request->getParsedBody() === null) {
            return ['errors' => [self::getError('invalidRequest')]];
        }

        return [];
    }

    public static function validateServiceLocationCombination(int $officeId, array $serviceIds): array
    {
        if ($officeId <= 0) {
            return ['errors' => [self::getError('invalidOfficeId')]];
        }

        if (empty($serviceIds) || !self::isValidNumericArray($serviceIds)) {
            return ['errors' => [self::getError('invalidServiceId')]];
        }

        $availableServices = ZmsApiFacadeService::getServicesProvidedAtOffice($officeId);
        $availableServiceIds = [];
        foreach ($availableServices as $service) {
            $availableServiceIds[] = $service->id;
        }

        $invalidServiceIds = array_diff($serviceIds, $availableServiceIds);
        return empty($invalidServiceIds)
            ? []
            : ['errors' => [self::getError('invalidLocationAndServiceCombination')]];
    }

    public static function validateGetBookableFreeDays(?array $officeIds, ?array $serviceIds, ?string $startDate, ?string $endDate, ?array $serviceCounts): array
    {
        $errors = [];
        if (!self::isValidOfficeIds($officeIds)) {
            $errors[] = self::getError('invalidOfficeId');
        }

        if (!self::isValidServiceIds($serviceIds)) {
            $errors[] = self::getError('invalidServiceId');
        }

        if (!$startDate || !self::isValidDate($startDate)) {
            $errors[] = self::getError('invalidStartDate');
        }

        if (!$endDate || !self::isValidDate($endDate)) {
            $errors[] = self::getError('invalidEndDate');
        }

        if ($startDate && $endDate && self::isValidDate($startDate) && self::isValidDate($endDate)) {
            if (new DateTime($startDate) > new DateTime($endDate)) {
                $errors[] = self::getError('startDateAfterEndDate');
            }

            if (!self::isDateRangeValid($startDate, $endDate)) {
                $errors[] = self::getError('dateRangeTooLarge');
            }
        }

        if (!self::isValidServiceCounts($serviceCounts)) {
            $errors[] = self::getError('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validateGetProcessById(?int $processId, ?string $authKey): array
    {
        $errors = [];
        if (!self::isValidProcessId($processId)) {
            $errors[] = self::getError('invalidProcessId');
        }

        if (!self::isValidAuthKey($authKey)) {
            $errors[] = self::getError('invalidAuthKey');
        }

        return ['errors' => $errors];
    }

    public static function validateGetAvailableAppointments(?string $date, ?array $officeIds, ?array $serviceIds, ?array $serviceCounts): array
    {
        $errors = [];
        if (!$date || !self::isValidDate($date)) {
            $errors[] = self::getError('invalidDate');
        }

        if (!self::isValidOfficeIds($officeIds)) {
            $errors[] = self::getError('invalidOfficeId');
        }

        if (!self::isValidServiceIds($serviceIds)) {
            $errors[] = self::getError('invalidServiceId');
        }

        if (!self::isValidServiceCounts($serviceCounts)) {
            $errors[] = self::getError('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validatePostAppointmentReserve(?int $officeId, ?array $serviceIds, ?array $serviceCounts, ?int $timestamp): array
    {
        $errors = [];
        if (!self::isValidOfficeId($officeId)) {
            $errors[] = self::getError('invalidOfficeId');
        }

        if (!self::isValidServiceIds($serviceIds)) {
            $errors[] = self::getError('invalidServiceId');
        }

        if (!self::isValidTimestamp($timestamp)) {
            $errors[] = self::getError('invalidTimestamp');
        }

        if (!self::isValidServiceCounts($serviceCounts)) {
            $errors[] = self::getError('invalidServiceCount');
        }

        return ['errors' => $errors];
    }

    public static function validateUpdateAppointmentInputs(?int $processId, ?string $authKey, ?string $familyName, ?string $email, ?string $telephone, ?string $customTextfield): array
    {
        $errors = [];
        if (!self::isValidProcessId($processId)) {
            $errors[] = self::getError('invalidProcessId');
        }

        if (!self::isValidAuthKey($authKey)) {
            $errors[] = self::getError('invalidAuthKey');
        }

        if (!self::isValidFamilyName($familyName)) {
            $errors[] = self::getError('invalidFamilyName');
        }

        if (!self::isValidEmail($email)) {
            $errors[] = self::getError('invalidEmail');
        }

        if (!self::isValidTelephone($telephone)) {
            $errors[] = self::getError('invalidTelephone');
        }

        if (!self::isValidCustomTextfield($customTextfield)) {
            $errors[] = self::getError('invalidCustomTextfield');
        }

        return ['errors' => $errors];
    }

    public static function validateGetScopeById(?int $scopeId): array
    {
        return !self::isValidScopeId($scopeId)
            ? ['errors' => [self::getError('invalidScopeId')]]
            : [];
    }

    public static function validateGetServicesByOfficeId(?int $officeId): array
    {
        return !self::isValidOfficeId($officeId)
            ? ['errors' => [self::getError('invalidOfficeId')]]
            : [];
    }

    public static function validateGetOfficeListByServiceId(?int $serviceId): array
    {
        return !self::isValidServiceId($serviceId)
            ? ['errors' => [self::getError('invalidServiceId')]]
            : [];
    }

    public static function validateGetProcessFreeSlots(?ProcessList $freeSlots): array
    {
        return empty($freeSlots) || !is_iterable($freeSlots)
            ? ['errors' => [self::getError('appointmentNotAvailable')]]
            : [];
    }

    public static function validateGetProcessByIdTimestamps(?array $appointmentTimestamps): array
    {
        return empty($appointmentTimestamps)
            ? ['errors' => [self::getError('appointmentNotAvailable')]]
            : [];
    }

    public static function validateGetProcessNotFound(?Process $process): array
    {
        return !$process
            ? ['errors' => [self::getError('appointmentNotAvailable')]]
            : [];
    }

    public static function validateScopesNotFound(?ScopeList $scopes): array
    {
        return empty($scopes) || $scopes === null || $scopes->count() === 0
            ? ['errors' => [self::getError('scopesNotFound')]]
            : [];
    }

    public static function validateServicesNotFound(?array $services): array
    {
        return empty($services)
            ? ['errors' => [self::getError('requestNotFound')]]
            : [];
    }

    public static function validateOfficesNotFound(?array $offices): array
    {
        return empty($offices)
            ? ['errors' => [self::getError('providerNotFound')]]
            : [];
    }

    public static function validateAppointmentDaysNotFound(?array $formattedDays): array
    {
        return empty($formattedDays)
            ? ['errors' => [self::getError('noAppointmentForThisDay')]]
            : [];
    }

    public static function validateNoAppointmentsAtLocation(): array
    {
        return ['errors' => [self::getError('noAppointmentsAtLocation')]];
    }

    public static function validateServiceArrays(array $serviceIds, array $serviceCounts): array
    {
        $errors = [];
        if (empty($serviceIds) || empty($serviceCounts)) {
            $errors[] = self::getError('emptyServiceArrays');
        }

        if (count($serviceIds) !== count($serviceCounts)) {
            $errors[] = self::getError('mismatchedArrays');
        }

        foreach ($serviceIds as $id) {
            if (!is_numeric($id)) {
                $errors[] = self::getError('invalidServiceId');
                break;
            }
        }

        foreach ($serviceCounts as $count) {
            if (!is_numeric($count) || $count < 0) {
                $errors[] = self::getError('invalidServiceCount');
                break;
            }
        }

        return $errors;
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

    private static function isValidOfficeIds(?array $officeIds): bool
    {
        return !empty($officeIds) && self::isValidNumericArray($officeIds);
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

    private static function isValidOfficeId(?int $officeId): bool
    {
        return !empty($officeId) && $officeId > 0;
    }

    private static function isValidServiceId(?int $serviceId): bool
    {
        return !empty($serviceId) && $serviceId > 0;
    }
}
