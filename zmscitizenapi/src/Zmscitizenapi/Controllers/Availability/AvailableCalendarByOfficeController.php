<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Availability;

use BO\Slim\LoggerService;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenapi\Services\Availability\AvailableCalendarByOfficeService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableCalendarByOfficeController extends BaseController
{
    use UnpublishedAccessTrait;

    private AvailableCalendarByOfficeService $service;

    public function __construct()
    {
        $this->initializeUnpublishedAccess();
        $this->service = new AvailableCalendarByOfficeService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $t0 = microtime(true);
        $traceId = bin2hex(random_bytes(8));
        $queryParams = $request->getQueryParams();

        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            LoggerService::logInfo('calendar.availability.timing', [
                'trace_id' => $traceId,
                'stage' => 'controller.total',
                'early_return' => 'invalid_request',
                'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            ]);
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $result = $this->service->getAvailableCalendarByOffice($queryParams, $this->showUnpublished, $traceId);

        $tAfterService = microtime(true);
        $isError = is_array($result) && isset($result['errors']);
        $payload = $isError ? $result : $result->toArray();
        $tAfterToArray = microtime(true);
        $responseOut = $this->createJsonResponse(
            $response,
            $payload,
            $isError ? ErrorMessages::getHighestStatusCode($result['errors']) : 200
        );

        LoggerService::logInfo('calendar.availability.timing', [
            'trace_id' => $traceId,
            'stage' => 'controller.total',
            'service_ms' => (int) round(($tAfterService - $t0) * 1000),
            'toArray_ms' => (int) round(($tAfterToArray - $tAfterService) * 1000),
            'json_ms' => (int) round((microtime(true) - $tAfterToArray) * 1000),
            'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            'office_count' => count(array_filter(explode(',', (string) ($queryParams['officeId'] ?? '')))),
            'service_count' => count(array_filter(explode(',', (string) ($queryParams['serviceId'] ?? '')))),
            'is_error' => $isError,
        ]);

        return $responseOut;
    }
}
