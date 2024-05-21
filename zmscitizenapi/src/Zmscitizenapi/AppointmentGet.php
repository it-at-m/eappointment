<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return Render::withJson($response, []);
    }
}
