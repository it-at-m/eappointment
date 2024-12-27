<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
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

        $result = ZmsApiFacadeService::getAvailableAppointments( $date, (int)$officeId, $serviceIds,$serviceCounts);
        if (!empty($result['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
            return $this->createJsonResponse($response, $result, $statusCode);
        }

        return $this->createJsonResponse($response, $result->toArray(), statusCode: 200);
    }

}
