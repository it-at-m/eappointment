<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Appointment;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Services\Appointment\AppointmentPreconfirmService;
use BO\Zmscitizenbackend\Services\Core\AuthenticationService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentPreconfirmController extends BaseController
{
    private AppointmentPreconfirmService $service;
    /** @psalm-api */
    public function __construct()
    {
        $this->service = new AppointmentPreconfirmService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $authenticatedUser = AuthenticationService::getAuthenticatedUser($request);
        $result = $this->service->processPreconfirm($request->getParsedBody(), $authenticatedUser);
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
