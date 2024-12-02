<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class ScopesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $scopes = ZmsApiFacadeService::getScopes();

        return $this->createJsonResponse($response, $scopes, statusCode: $scopes['status']);
    }
}
