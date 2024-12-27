<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeById extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $queryParams = $request->getQueryParams();
        $scopeId = isset($queryParams['scopeId']) && is_numeric($queryParams['scopeId']) ? (int)$queryParams['scopeId'] : null;

        $errors = ValidationService::validateScopeIdParam($scopeId);
        if (!empty($errors)) {
            return $this->createJsonResponse($response, $errors, 400);
        }

        $result = ZmsApiFacadeService::getScopeById((int) $scopeId);
        if (!empty($result['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
            return $this->createJsonResponse($response, $result, $statusCode);
        }

        return $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
