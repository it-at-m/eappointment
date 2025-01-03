<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeById extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $clientData = $this->extractClientData($request->getQueryParams());
        
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            $result = $this->getScope($clientData);
            
            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof ThinnedScope
            ? $this->createJsonResponse($response, $result->toArray(), 200)
            : $this->createJsonResponse(
                $response, 
                ErrorMessages::get('invalidRequest'), 
                ErrorMessages::get('invalidRequest')['statusCode']
            );
            
        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('internalError')]],
                500
            );
        }
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'scopeId' => isset($queryParams['scopeId']) && is_numeric($queryParams['scopeId']) 
                ? (int)$queryParams['scopeId'] 
                : null
        ];
    }

    private function validateClientData(object $clientData): array
    {
        return ValidationService::validateGetScopeById($clientData->scopeId);
    }

    private function getScope(object $clientData): array|ThinnedScope
    {
        return ZmsApiFacadeService::getScopeById($clientData->scopeId);
    }
}