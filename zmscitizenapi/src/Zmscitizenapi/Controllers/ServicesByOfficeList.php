<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class ServicesByOfficeList extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $officeIds = explode(',', $request->getQueryParams()['officeId'] ?? '');

        $result = ZmsApiFacadeService::getServicesByOfficeIds($officeIds);
        if (!empty($result['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
            return $this->createJsonResponse($response, $result, $statusCode);
        }
    
        return $this->createJsonResponse($response, $result->toArray(), 200);
    }

}
