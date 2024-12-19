<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class AppointmentById extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $processId = isset($queryParams['processId']) && is_numeric($queryParams['processId']) ? (int)$queryParams['processId'] : null;
        $authKey = isset($queryParams['authKey']) && is_string($queryParams['authKey']) && trim($queryParams['authKey']) !== '' ? $queryParams['authKey'] : null;
    
        $result = ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    
        if (!empty($result['errors'])) {
            $errorCodes = array_column($result['errors'], 'errorCode');
            $statusCode = in_array('appointmentNotFound', $errorCodes) ? 404 : 400; 
            return $this->createJsonResponse($response, $result, $statusCode);
        }
    
        if (isset($result) && $result instanceof ThinnedProcess) {
            $thinnedProcess = $result;
            return $this->createJsonResponse($response, $thinnedProcess->toArray(), 200);
        }
    
        return $this->createJsonResponse($response, $result, 400);
    }
    
}
