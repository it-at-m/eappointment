<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
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
        try {
            $requestErrors = ValidationService::validateServerPostRequest($request);
            if (!empty($requestErrors['errors'])) {
                return $this->createJsonResponse(
                    $response,
                    $requestErrors,
                    ErrorMessages::get('invalidRequest')['statusCode']
                );
            }

            $result = $this->service->processReservation($request->getParsedBody());

            return is_array($result) && isset($result['errors'])
                ? $this->createJsonResponse(
                    $response,
                    $result,
                    ErrorMessages::getHighestStatusCode($result['errors'])
                )
                : $this->createJsonResponse($response, $result->toArray(), 200);

        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('internalError')]],
                500
            );
        }
    }
}