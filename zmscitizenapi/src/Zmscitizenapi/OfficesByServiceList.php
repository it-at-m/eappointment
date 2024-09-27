<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class OfficesByServiceList extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $serviceIds = explode(',', $request->getQueryParams()['serviceId'] ?? '');

        $result = ZmsApiFacadeService::getOfficesByServiceIds($serviceIds);

        if (isset($result['error'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }

        return $this->createJsonResponse($response, $result['offices'], $result['status']);
    }

}
