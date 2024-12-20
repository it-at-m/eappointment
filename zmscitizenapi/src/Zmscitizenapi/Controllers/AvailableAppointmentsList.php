<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class AvailableAppointmentsList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $date = $queryParams['date'] ?? null;
        $officeId = $queryParams['officeId'] ?? null;
        $serviceIds = isset($queryParams['serviceId']) ? explode(',', $queryParams['serviceId']) : [];
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];

        $result = ZmsApiFacadeService::getAvailableAppointments( $date, $officeId, $serviceIds,$serviceCounts);
        if (isset($result['errors'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }

        return $this->createJsonResponse($response, $result->toArray(), statusCode: 200);
    }

}
