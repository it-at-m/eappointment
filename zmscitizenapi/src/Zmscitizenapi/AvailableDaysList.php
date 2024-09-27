<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class AvailableDaysList extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();

        $result = ZmsApiFacadeService::getBookableFreeDays($queryParams);
        
        return $this->createJsonResponse($response, $result, $result['status']);
    }

}
