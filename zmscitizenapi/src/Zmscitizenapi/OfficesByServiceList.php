<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\ValidationService;

class OfficesByServiceList extends BaseController
{

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $serviceIdParam = $request->getQueryParams()['serviceId'] ?? [];

        if (is_string($serviceIdParam)) {
            $serviceIdParam = explode(',', $serviceIdParam);
        }
    
        $errors = ValidationService::validateServiceIdParam($serviceIdParam);
        if (!empty($errors)) {
            return $this->createJsonResponse($response, ['errors' => $errors], 400);
        }

        $result = ZmsApiFacadeService::getOfficesByServiceIds($serviceIdParam);
    
        if (isset($result['errors'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }
    
        return $this->createJsonResponse($response, $result['offices'], $result['status']);
    }
    
    

}
