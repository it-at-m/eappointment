<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentPreconfirm extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return Render::withJson($response, []);
    }
}