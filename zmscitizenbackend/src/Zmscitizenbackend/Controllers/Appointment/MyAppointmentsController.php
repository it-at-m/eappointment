<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Appointment;

use BO\Mellon\Validator;
use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Services\Core\AuthenticationService;
use BO\Zmscitizenbackend\Services\Appointment\MyAppointmentsService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyAppointmentsController extends BaseController
{
    private MyAppointmentsService $service;

    /** @psalm-api */
    public function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->service = new MyAppointmentsService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }

        $filterId = Validator::param('filterId')->isNumber()->getValue();
        $user = AuthenticationService::getAuthenticatedUser($request);
        if (is_null($user)) {
            return $this->createJsonResponse($response, [
                'errors' => [ErrorMessages::get('authKeyMismatch')]
            ], 401);
        }

        $appointments = $this->service->getAppointmentsForUser($user, $filterId);
        $payload = array_map(
            static fn (ThinnedProcess $appointment): array => $appointment->toArray(),
            $appointments
        );

        return $this->createJsonResponse($response, $payload, 200);
    }
}
