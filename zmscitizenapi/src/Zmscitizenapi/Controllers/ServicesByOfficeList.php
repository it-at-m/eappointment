<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ServicesService;

class ServicesByOfficeList extends BaseController
{
    protected $servicesService;

    public function __construct()
    {
        $this->servicesService = new ServicesService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $officeIds = explode(',', $request->getQueryParams()['officeId'] ?? '');

        $result = $this->servicesService->getServicesByOfficeIds($officeIds);

        if (isset($result['error'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }
    
        return $this->createJsonResponse($response, $result['services'], $result['status']);
    }

    private function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
}
