<?php

namespace BO\Zmscitizenapi\Services;

class OfficesService
{
    public function getOffices()
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = $sources->getScopeList() ?? [];
        $providerProjectionList = [];

        foreach ($sources->getProviderList() as $provider) {
            $matchingScope = null;
            foreach ($scopeList as $scope) {
                if ($scope->provider->id == $provider->id) {
                    $matchingScope = $scope;
                    break;
                }
            }

            $providerData = [
                "id" => $provider->id,
                "name" => $provider->displayName ?? $provider->name,
            ];

            if ($matchingScope) {
                $providerData["scope"] = [
                    "id" => $matchingScope->id,
                    "provider" => $matchingScope->provider,
                    "shortName" => $matchingScope->shortName,
                    "telephoneActivated" => $matchingScope->getTelephoneActivated(),
                    "telephoneRequired" => $matchingScope->getTelephoneRequired(),
                    "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated(),
                    "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired(),
                    "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel(),
                    "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired(),
                    "displayInfo" => $matchingScope->getDisplayInfo()
                ];
            }

            $providerProjectionList[] = $providerData;
        }

        return $providerProjectionList;
    }

    public function getOfficesByServiceIds(array $serviceIds)
    {
        $serviceIds = array_unique($serviceIds);

        if (empty($serviceIds) || $serviceIds == ['']) {
            return [
                'offices' => [],
                'error' => 'Invalid serviceId(s)',
                'status' => 400
            ];
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $providerList = $sources->getProviderList();
        $requestRelationList = $sources->getRequestRelationList();

        $offices = [];
        $notFoundIds = [];
        $addedOfficeIds = [];

        foreach ($serviceIds as $serviceId) {
            $found = false;
            foreach ($requestRelationList as $relation) {
                if ($relation->request->id == $serviceId) {
                    if (!in_array($relation->provider->id, $addedOfficeIds)) {
                        foreach ($providerList as $provider) {
                            if ($provider->id == $relation->provider->id) {
                                $offices[] = [
                                    "id" => $provider->id,
                                    "name" => $provider->name,
                                ];
                                $addedOfficeIds[] = $provider->id;
                                $found = true;
                                break;
                            }
                        }
                    } else {
                        $found = true;
                    }
                }
            }
            if (!$found) {
                $notFoundIds[] = $serviceId;
            }
        }

        if (empty($offices)) {
            return [
                'offices' => [],
                'error' => 'Office(s) not found for the provided serviceId(s)',
                'status' => 404
            ];
        }

        $responseContent = ['offices' => $offices];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following serviceId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        return [
            'offices' => $responseContent,
            'status' => 200
        ];
    }
}
