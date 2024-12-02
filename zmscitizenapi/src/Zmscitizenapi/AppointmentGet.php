<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class AppointmentGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $processId = isset($queryParams['processId']) && is_numeric($queryParams['processId']) ? (int)$queryParams['processId'] : null;
        $authKey = isset($queryParams['authKey']) && is_string($queryParams['authKey']) && trim($queryParams['authKey']) !== '' ? $queryParams['authKey'] : null;

        $result = ZmsApiFacadeService::getProcessById($processId, $authKey);

        return $this->createJsonResponse($response, $result['data'] ?? $result, $result['status']);
    }
    
}
