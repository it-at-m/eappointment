<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Appointment;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Services\Appointment\AppointmentUpdateService;
use BO\Zmscitizenbackend\Services\Core\AuthenticationService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentUpdateController extends BaseController
{
    private AppointmentUpdateService $service;

    /** @psalm-api */
    public function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->service = new AppointmentUpdateService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }

        $authenticatedUser = AuthenticationService::getAuthenticatedUser($request);
        $result = $this->service->processUpdate($request->getParsedBody(), $authenticatedUser);
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse(
                $response,
                $result,
                ErrorMessages::getHighestStatusCode($result['errors'])
            )
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
