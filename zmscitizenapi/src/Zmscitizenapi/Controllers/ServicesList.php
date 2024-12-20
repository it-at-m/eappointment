<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class ServicesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $services = ZmsApiFacadeService::getServices();

        return $this->createJsonResponse($response, $services->toArray(), statusCode: 200);
    }
}
