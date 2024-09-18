<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ScopesService;

class ScopesList extends BaseController
{
    protected $scopesService;

    public function __construct()
    {
        $this->scopesService = new ScopesService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $scopes = $this->scopesService->getScopes();
        return Render::withJson($response, ["scopes" => $scopes]);
    }
}
