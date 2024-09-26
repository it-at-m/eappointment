<?php

namespace BO\Zmscitizenapi\Services;

class ScopesService
{
    public function getScopes()
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = $sources->getScopeList() ?? [];
        $scopesProjectionList = [];

        foreach ($scopeList as $scope) {
            $scopesProjectionList[] = [
                "id" => $scope->id,
                "provider" => $scope->provider,
                "shortName" => $scope->shortName,
                "telephoneActivated" => $scope->getTelephoneActivated(),
                "telephoneRequired" => $scope->getTelephoneRequired(),
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated(),
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired(),
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel(),
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired(),
                "displayInfo" => $scope->getDisplayInfo()
            ];
        }

        return $scopesProjectionList;
    }

    public function getScopeByIds(array $scopeIds)
    {
        $scopeIds = array_unique($scopeIds);

        if (empty($scopeIds) || $scopeIds == ['']) {
            return [
                'scopes' => [],
                'error' => 'Invalid scopeId(s)',
                'status' => 400
            ];
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = $sources->getScopeList();
        $scopes = [];
        $notFoundIds = [];

        foreach ($scopeIds as $scopeId) {
            $found = false;
            foreach ($scopeList as $scopeItem) {
                if ($scopeItem->id == $scopeId) {
                    $scopes[] = [
                        "id" => $scopeItem->id,
                        "provider" => [
                            "id" => $scopeItem->provider->id,
                            "source" => $scopeItem->provider->source,
                        ],
                        "shortName" => $scopeItem->shortName,
                        "telephoneActivated" => $scopeItem->getTelephoneActivated(),
                        "telephoneRequired" => $scopeItem->getTelephoneRequired(),
                        "customTextfieldActivated" => $scopeItem->getCustomTextfieldActivated(),
                        "customTextfieldRequired" => $scopeItem->getCustomTextfieldRequired(),
                        "customTextfieldLabel" => $scopeItem->getCustomTextfieldLabel(),
                        "captchaActivatedRequired" => $scopeItem->getCaptchaActivatedRequired(),
                        "displayInfo" => $scopeItem->getDisplayInfo()
                    ];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $notFoundIds[] = $scopeId;
            }
        }

        if (empty($scopes)) {
            return [
                'scopes' => [],
                'error' => 'Scope(s) not found',
                'status' => 404
            ];
        }

        $responseContent = ['scopes' => $scopes];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following scopeId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        return [
            'scopes' => $responseContent,
            'status' => 200
        ];
    }

    public function getScopeByOfficeId($officeId)
    {
        try {
            $scopes = $this->getScopes();
            
            foreach ($scopes as $scope) {
                if (isset($scope['provider']) && $scope['provider']->id == $officeId) {
                    return $scope;
                }
            }

            return [
                'error' => 'Scope not found for the provided office ID',
                'status' => 404
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Error fetching scope by office ID: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function mapScope($scope)
    {
        return [
            'id' => $scope['id'] ?? null,
            'provider' => [
                'id' => $scope['provider']['id'] ?? null,
                'source' => $scope['provider']['source'] ?? null,
            ],
            'shortName' => $scope['shortName'] ?? null,
            'telephoneActivated' => $scope['telephoneActivated'] ?? null,
            'telephoneRequired' => $scope['telephoneRequired'] ?? null,
            'customTextfieldActivated' => $scope['customTextfieldActivated'] ?? null,
            'customTextfieldRequired' => $scope['customTextfieldRequired'] ?? null,
            'customTextfieldLabel' => $scope['customTextfieldLabel'] ?? null,
            'captchaActivatedRequired' => $scope['captchaActivatedRequired'] ?? null,
            'displayInfo' => $scope['displayInfo'] ?? null,
        ];
    }
}
