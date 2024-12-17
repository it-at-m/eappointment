<?php

namespace BO\Zmscitizenapi\Controllers;

use \BO\Zmscitizenapi\BaseController;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use \BO\Zmscitizenapi\Services\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeById extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $scopeIdParam = $request->getQueryParams()['scopeId'] ?? [];

        if (is_string($scopeIdParam)) {
            $scopeIdParam = explode(',', $scopeIdParam);
        }

        $errors = ValidationService::validateScopeIdParam($scopeIdParam);
        if (!empty($errors)) {
            return $this->createJsonResponse($response, $errors, 400);
        }

        $result = ZmsApiFacadeService::getScopeByIds($scopeIdParam);

        if (isset($result['errors'])) {
            return $this->createJsonResponse($response, $result, $result['status']);
        }

        return $this->createJsonResponse($response, $result['scopes'], $result['status']);
    }
}
