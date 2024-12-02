<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class OfficesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $offices = ZmsApiFacadeService::getOffices();

        return $this->createJsonResponse($response, $offices, statusCode: $offices['status']);
    }
}
