<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class OfficesServicesRelations extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $data = ZmsApiFacadeService::getServicesAndOffices();

        return Render::withJson($response, $data);
    }
}
