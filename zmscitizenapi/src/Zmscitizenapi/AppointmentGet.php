<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class AppointmentGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $processId = $queryParams['processId'] ?? null;
        $authKey = $queryParams['authKey'] ?? null;

        $result = ZmsApiFacadeService::getProcessById($processId, $authKey);

        return $this->createJsonResponse($response, $result['data'] ?? $result, $result['status']);
    }
    
}
