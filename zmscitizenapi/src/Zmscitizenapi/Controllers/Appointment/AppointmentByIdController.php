<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Appointment\AppointmentByIdService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentByIdController extends BaseController
{
    private AppointmentByIdService $service;

    public function __construct()
    {
        $this->service = new AppointmentByIdService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $requestErrors = ValidationService::validateServerGetRequest($request);
            if (!empty($requestErrors['errors'])) {
                return $this->createJsonResponse(
                    $response,
                    $requestErrors,
                    ErrorMessages::get('invalidRequest')['statusCode']
                );
            }

            $result = $this->service->getAppointmentById($request->getQueryParams());

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