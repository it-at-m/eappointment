<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class OfficesServicesRelations extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $officesAndServices = ZmsApiFacadeService::getServicesAndOffices();

        return $this->createJsonResponse($response, $officesAndServices, statusCode: $officesAndServices['status']);
    }
}
