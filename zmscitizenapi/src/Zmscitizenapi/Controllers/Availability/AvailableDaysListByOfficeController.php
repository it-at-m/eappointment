<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Availability;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Helper\ErrorMessages;
use BO\Zmscitizenapi\Services\Availability\AvailableDaysListService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableDaysListByOfficeController extends BaseController
{
    private AvailableDaysListService $service;

    public function __construct()
    {
        $this->service = new AvailableDaysListService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }

        $result = $this->service->getAvailableDaysListByOffice($request->getQueryParams());

        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
