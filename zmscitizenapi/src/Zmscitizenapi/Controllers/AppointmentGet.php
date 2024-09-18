<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\AppointmentService;
use BO\Zmscitizenapi\Services\ProcessService;

class AppointmentGet extends BaseController
{
    protected $appointmentService;

    public function __construct()
    {
        $processService = new ProcessService(\App::$http);
        $this->appointmentService = new AppointmentService($processService);
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $processId = $queryParams['processId'] ?? null;
        $authKey = $queryParams['authKey'] ?? null;

        $result = $this->appointmentService->getAppointmentById($processId, $authKey);

        return $this->createJsonResponse($response, $result['data'] ?? $result, $result['status']);
    }
    
}
