<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Mellon\Validator;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\Core\AuthenticationService;
use BO\Zmscitizenapi\Services\Appointment\MyAppointmentsService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyAppointmentsController extends BaseController
{
    private MyAppointmentsService $service;
    public function __construct()
    {
        $this->service = new MyAppointmentsService();
    }

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
