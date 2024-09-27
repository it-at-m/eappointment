<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeByIdGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $scopeIds = explode(',', $request->getQueryParams()['scopeId'] ?? '');
    
        $result = ZmsApiFacadeService::getScopeByIds($scopeIds);
    
        if (isset($result['errors'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }

        return $this->createJsonResponse($response, $result['scopes'], $result['status']);
    }
    
}
