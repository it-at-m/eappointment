<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\OfficesServicesRelationsService;

class OfficesServicesRelations extends BaseController
{
    protected $officesServicesRelationsService;

    public function __construct()
    {
        $this->officesServicesRelationsService = new OfficesServicesRelationsService(); // No container
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $data = $this->officesServicesRelationsService->getOfficesServicesRelations($sources);

        return Render::withJson($response, $data);
    }
}
