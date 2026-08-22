<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Appointment;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Services\Appointment\AppointmentByIdService;
use BO\Zmscitizenbackend\Services\Core\AuthenticationService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentByIdController extends BaseController
{
    private AppointmentByIdService $service;
    /** @psalm-api */
    public function __construct()
    {
        $this->service = new AppointmentByIdService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $authenticatedUser = AuthenticationService::getAuthenticatedUser($request);
        $result = $this->service->getAppointmentById($request->getQueryParams(), $authenticatedUser);
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
