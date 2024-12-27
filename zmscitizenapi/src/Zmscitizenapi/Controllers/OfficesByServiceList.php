<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
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
            return $this->createJsonResponse($response, $errors, 400);
        }

        $result = ZmsApiFacadeService::getOfficesByServiceIds($serviceIdParam);
        if (!empty($result['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
            return $this->createJsonResponse($response, $result, $statusCode);
        }
    
        return $this->createJsonResponse($response, $result->toArray(), 200);
    }
    
    

}
