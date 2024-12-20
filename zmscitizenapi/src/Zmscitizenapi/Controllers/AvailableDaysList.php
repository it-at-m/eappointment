<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class AvailableDaysList extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $result = ZmsApiFacadeService::getBookableFreeDays($queryParams);
        if (isset($result['errors'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }
        
        return $this->createJsonResponse($response, $result->toArray(), 200);
    }

}
