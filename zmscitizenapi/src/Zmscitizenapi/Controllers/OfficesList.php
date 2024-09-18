<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\OfficesService;

class OfficesList extends BaseController
{
    protected $officesService;

    public function __construct()
    {
        $this->officesService = new OfficesService(); // No container
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $offices = $this->officesService->getOffices();
        return Render::withJson($response, ["offices" => $offices]);
    }
}
