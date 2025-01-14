<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Appointment\AppointmentCancelService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentCancelController extends BaseController
{
    private AppointmentCancelService $service;

    public function __construct()
    {
        $this->service = new AppointmentCancelService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest', $this->language)['statusCode']
            );
        }
    
        $result = $this->service->processCancel($request->getParsedBody());
    
        // Handle array errors from validation
        if (is_array($result) && isset($result['errors'])) {
            foreach ($result['errors'] as &$error) {
                if (isset($error['errorCode'])) {
                    $error = ErrorMessages::get($error['errorCode'], $this->language);
                }
            }
            return $this->createJsonResponse(
                $response,
                $result,
                ErrorMessages::getHighestStatusCode($result['errors'])
            );
        }
    
        // Handle successful response
        return $this->createJsonResponse($response, $result->toArray(), 200);
    }
}