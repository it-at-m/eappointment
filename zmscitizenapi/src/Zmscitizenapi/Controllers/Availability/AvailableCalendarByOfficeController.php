<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Availability;

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
        $queryParams = $request->getQueryParams();

        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $result = $this->service->getAvailableCalendarByOffice($queryParams, $this->showUnpublished);

        $isError = is_array($result) && isset($result['errors']);
        return $this->createJsonResponse(
            $response,
            $isError ? $result : $result->toArray(),
            $isError ? ErrorMessages::getHighestStatusCode($result['errors']) : 200
        );
    }
}
