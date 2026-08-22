<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Appointment;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Services\Appointment\AppointmentReserveService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentReserveController extends BaseController
{
    use UnpublishedAccessTrait;

    private AppointmentReserveService $service;

    /** @psalm-api */
    public function __construct()
    {
        $this->initializeUnpublishedAccess();
        $this->service = new AppointmentReserveService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $result = $this->service->processReservation($request->getParsedBody(), $this->showUnpublished);
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
