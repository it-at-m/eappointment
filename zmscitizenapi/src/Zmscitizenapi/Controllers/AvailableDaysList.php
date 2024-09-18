<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\AvailableDaysService;

class AvailableDaysList extends BaseController
{
    protected $availableDaysService;

    public function __construct()
    {
        $this->availableDaysService = new AvailableDaysService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();

        $result = $this->availableDaysService->getAvailableDays($queryParams);
        
        return $this->createJsonResponse($response, $result, $result['status']);
    }

}
