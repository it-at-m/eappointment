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
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $result = $this->service->getAvailableCalendarByOffice($request->getQueryParams(), $this->showUnpublished);

        $tAfterService = microtime(true);
        $isError = is_array($result) && isset($result['errors']);
        $payload = $isError ? $result : $result->toArray();
        $responseOut = $this->createJsonResponse(
            $response,
            $payload,
            $isError ? ErrorMessages::getHighestStatusCode($result['errors']) : 200
        );

        LoggerService::logInfo('calendar.availability.timing', [
            'stage' => 'controller.total',
            'service_ms' => (int) round(($tAfterService - $t0) * 1000),
            'toArray_ms' => (int) round((microtime(true) - $tAfterService) * 1000),
            'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            'office_count' => count(array_filter(explode(',', (string) ($request->getQueryParams()['officeId'] ?? '')))),
            'service_count' => count(array_filter(explode(',', (string) ($request->getQueryParams()['serviceId'] ?? '')))),
            'is_error' => $isError,
        ]);

        return $responseOut;
    }
}
