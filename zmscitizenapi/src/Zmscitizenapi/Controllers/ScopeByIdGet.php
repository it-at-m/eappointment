<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\ScopesService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeByIdGet extends BaseController
{
    protected $scopesService;

    public function __construct()
    {
        $this->scopesService = new ScopesService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $scopeIds = explode(',', $request->getQueryParams()['scopeId'] ?? '');
    
        $result = $this->scopesService->getScopeByIds($scopeIds);
    
        if (isset($result['error'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }        

        return $this->createJsonResponse($response, $result['scopes'], $result['status']);
    }

    private function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
    
}
