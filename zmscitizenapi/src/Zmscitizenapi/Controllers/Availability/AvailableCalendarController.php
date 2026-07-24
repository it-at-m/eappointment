<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Availability;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenapi\Services\Availability\AvailableCalendarService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableCalendarController extends BaseController
{
    use UnpublishedAccessTrait;

    private AvailableCalendarService $service;

    public function __construct()
    {
        $this->initializeUnpublishedAccess();
        $this->service = new AvailableCalendarService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $result = $this->service->getAvailableCalendar($queryParams, $this->showUnpublished);

        $isError = is_array($result) && isset($result['errors']);
        return $this->createJsonResponse(
            $response,
            $isError ? $result : $result->toArray(),
            $isError ? ErrorMessages::getHighestStatusCode($result['errors']) : 200
        );
    }
}
