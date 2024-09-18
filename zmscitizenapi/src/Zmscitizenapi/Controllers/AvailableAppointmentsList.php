<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\AvailableAppointmentsService;

class AvailableAppointmentsList extends BaseController
{
    protected $availableAppointmentsService;

    public function __construct()
    {
        $this->availableAppointmentsService = new AvailableAppointmentsService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();

        $result = $this->availableAppointmentsService->getAvailableAppointments($queryParams);

        return $this->createJsonResponse($response, $result, $result['status']);
    }

}
