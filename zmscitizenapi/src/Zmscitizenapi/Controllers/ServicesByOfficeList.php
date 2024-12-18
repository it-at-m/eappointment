<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class ServicesByOfficeList extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $officeIds = explode(',', $request->getQueryParams()['officeId'] ?? '');

        $result = ZmsApiFacadeService::getServicesByOfficeIds($officeIds);

        if (isset($result['errors'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }
    
        return $this->createJsonResponse($response, $result->toArray(), $result->status);
    }

}
