<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Slim\LoggerService;
use BO\Zmscitizenapi\Models\AvailableCalendarByOffice;
use BO\Zmscitizenapi\Services\Captcha\CaptchaRequirementTrait;
use BO\Zmscitizenapi\Services\Captcha\TokenValidationService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class AvailableCalendarByOfficeService
{
    use CaptchaRequirementTrait;
    use ServiceLocationValidationTrait;

    private TokenValidationService $tokenValidator;
    private ZmsApiFacadeService $zmsApiFacadeService;

    public function __construct()
    {
        $this->tokenValidator = new TokenValidationService();
        $this->zmsApiFacadeService = new ZmsApiFacadeService();
    }

    public function getAvailableCalendarByOffice(
        array $queryParams,
        bool $showUnpublished = false,
        ?string $traceId = null
    ): AvailableCalendarByOffice|array {
        $t0 = microtime(true);
        $traceId = $traceId ?? bin2hex(random_bytes(8));
        $clientData = $this->extractClientData($queryParams);

        $t1 = microtime(true);
        $errors = $this->validateClientData($clientData);
        $validateClientMs = (int) round((microtime(true) - $t1) * 1000);
        if (!empty($errors['errors'])) {
            LoggerService::logInfo('calendar.availability.timing', [
                'trace_id' => $traceId,
                'stage' => 'service.validateClientData',
                'ms' => $validateClientMs,
                'early_return' => 'client_validation',
            ]);
            return $errors;
        }

        $t2 = microtime(true);
        $errors = $this->validateServiceLocations($clientData->officeIds, $clientData->serviceIds, $showUnpublished);
        $validateLocationsMs = (int) round((microtime(true) - $t2) * 1000);
        if ($errors !== null) {
            LoggerService::logInfo('calendar.availability.timing', [
                'trace_id' => $traceId,
                'stage' => 'service.validateServiceLocations',
                'ms' => $validateLocationsMs,
                'office_count' => count($clientData->officeIds),
                'early_return' => 'location_validation',
            ]);
            return $errors;
        }

        $t3 = microtime(true);
        $result = ZmsApiFacadeService::getCalendarAvailability(
            $clientData->officeIds,
            $clientData->serviceIds,
            $clientData->serviceCounts,
            $clientData->startDate,
            $clientData->endDate,
            $traceId,
            $clientData->slotsStartDate,
            $clientData->slotsEndDate
        );

        LoggerService::logInfo('calendar.availability.timing', [
            'trace_id' => $traceId,
            'stage' => 'service.total',
            'extract_ms' => (int) round(($t1 - $t0) * 1000),
            'validate_client_ms' => $validateClientMs,
            'validate_locations_ms' => $validateLocationsMs,
            'facade_ms' => (int) round((microtime(true) - $t3) * 1000),
            'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            'office_count' => count($clientData->officeIds),
            'service_count' => count($clientData->serviceIds),
            'slots_start_date' => $clientData->slotsStartDate,
            'slots_end_date' => $clientData->slotsEndDate,
        ]);

        return $result;
    }

    private function extractClientData(array $queryParams): object
    {
        $queryParams['officeId'] = isset($queryParams['officeId']) ? (string) $queryParams['officeId'] : '';
        $queryParams['serviceId'] = isset($queryParams['serviceId']) ? (string) $queryParams['serviceId'] : '';
        $serviceCount = $queryParams['serviceCount'] ?? '';
        $serviceCounts = !empty($serviceCount)
            ? array_map('trim', explode(',', (string) $serviceCount))
            : [];

        $slotsStartDate = isset($queryParams['slotsStartDate']) && $queryParams['slotsStartDate'] !== ''
            ? (string) $queryParams['slotsStartDate']
            : null;
        $slotsEndDate = isset($queryParams['slotsEndDate']) && $queryParams['slotsEndDate'] !== ''
            ? (string) $queryParams['slotsEndDate']
            : null;

        return (object) [
            'officeIds' => array_map('trim', explode(',', $queryParams['officeId'])),
            'serviceIds' => array_map('trim', explode(',', $queryParams['serviceId'])),
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
