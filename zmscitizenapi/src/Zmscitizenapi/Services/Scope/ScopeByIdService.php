<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class ScopeByIdService
{
    public function getScope(array $queryParams): ThinnedScope|array
    {
        $clientData = $this->extractClientData($queryParams);
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getScopeById($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'scopeId' => isset($queryParams['scopeId']) && is_numeric($queryParams['scopeId'])
                ? (int) $queryParams['scopeId']
                : null
        ];
    }

    private function validateClientData(object $clientData): array
    {
        return ValidationService::validateGetScopeById($clientData->scopeId);
    }

    private function getScopeById(object $clientData): array|ThinnedScope
    {
        return ZmsApiFacadeService::getScopeById($clientData->scopeId);
    }
}
