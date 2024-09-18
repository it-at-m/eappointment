<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ServicesService;

class ServicesList extends BaseController
{
    protected $servicesService;

    public function __construct()
    {
        $this->servicesService = new ServicesService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $services = $this->servicesService->getServices();
        return Render::withJson($response, ["services" => $services]);
    }
}
