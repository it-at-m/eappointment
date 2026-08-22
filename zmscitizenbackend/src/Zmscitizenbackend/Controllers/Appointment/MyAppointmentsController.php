<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Appointment;

use BO\Mellon\Validator;
use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Services\Core\AuthenticationService;
use BO\Zmscitizenbackend\Services\Appointment\MyAppointmentsService;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyAppointmentsController extends BaseController
{
    private MyAppointmentsService $service;
    /** @psalm-api */
    public function __construct()
    {
        $this->service = new MyAppointmentsService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $filterId = Validator::param('filterId')->isNumber()->getValue();
        $user = AuthenticationService::getAuthenticatedUser($request);
        if (is_null($user)) {
            return $this->createJsonResponse($response, [
                'errors' => [ErrorMessages::get('authKeyMismatch')]
            ], 401);
        }
        $result = $this->service->getAppointmentsForUser($user, $filterId);
        return $this->createJsonResponse($response, $result, 200);
    }
}
