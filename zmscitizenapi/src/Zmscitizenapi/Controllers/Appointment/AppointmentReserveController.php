<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Services\Appointment\AppointmentReserveService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentReserveController extends BaseController
{
    private AppointmentReserveService $service;

    public function __construct()
    {
        $this->service = new AppointmentReserveService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }

        $result = $this->service->processReservation($request->getParsedBody());
        if (is_array($result) && isset($result['errors'])) {
            foreach ($result['errors'] as &$error) {
                if (isset($error['errorCode'])) {
                    $error = ErrorMessages::get($error['errorCode']);
                }
            }
            return $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']));
        }

        return $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
