<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\OfficesService;

class OfficesByServiceList extends BaseController
{
    protected $officesService;

    public function __construct()
    {
        $this->officesService = new OfficesService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $serviceIds = explode(',', $request->getQueryParams()['serviceId'] ?? '');

        $result = $this->officesService->getOfficesByServiceIds($serviceIds);

        if (isset($result['error'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }

        return $this->createJsonResponse($response, $result['offices'], $result['status']);
    }

}
