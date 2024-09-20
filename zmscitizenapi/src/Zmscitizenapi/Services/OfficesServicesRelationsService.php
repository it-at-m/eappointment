<?php

namespace BO\Zmscitizenapi\Services;

class OfficesServicesRelationsService
{
    public function getOfficesServicesRelations($sources)
    {
        $providerList = $sources->getProviderList() ?? [];
        $requestList = $sources->getRequestList() ?? [];
        $relationList = $sources->getRequestRelationList() ?? [];

        $offices = $this->mapOfficesWithScope($sources, $providerList);
        $services = $this->mapServicesWithCombinations($requestList, $relationList);
        $relations = $this->mapRelations($relationList);

        return [
            'offices' => $offices,
            'services' => $services,
            'relations' => $relations,
        ];
    }

    private function mapOfficesWithScope($sources, $providerList)
    {
        $offices = [];
        foreach ($providerList as $provider) {
            $officeData = [
                "id" => $provider->id,
                "name" => $provider->displayName ?? $provider->name,
            ];
            $scope = $this->getScopeForProvider($sources, $provider->id);
            if ($scope) {
                $officeData['scope'] = $scope;
            }

            $offices[] = $officeData;
        }
        return $offices;
    }

    private function mapServicesWithCombinations($requestList, $relationList)
    {
        $servicesProviderIds = [];
        foreach ($relationList as $relation) {
            if (!isset($servicesProviderIds[$relation->request->id])) {
                $servicesProviderIds[$relation->request->id] = [];
            }
            $servicesProviderIds[$relation->request->id][] = $relation->provider->id;
        }

        $services = [];
        foreach ($requestList as $service) {
            $serviceCombinations = [];
            $mappedService = [
                "id" => $service->getId(),
                "name" => $service->getName(),
                "maxQuantity" => $service->getAdditionalData()['maxQuantity'] ?? 1,
            ];

            if (isset($service->getAdditionalData()['combinable'])) {
                foreach ($service->getAdditionalData()['combinable'] as $combinationServiceId) {
                    $commonProviders = array_intersect(
                        $servicesProviderIds[$service->getId()] ?? [],
                        $servicesProviderIds[$combinationServiceId] ?? []
                    );
                    $serviceCombinations[$combinationServiceId] = !empty($commonProviders) ? array_values($commonProviders) : [];
                }
                $mappedService['combinable'] = $serviceCombinations;
            }

            $services[] = $mappedService;
        }

        return $services;
    }

    private function mapRelations($relationList)
    {
        $relations = [];
        foreach ($relationList as $relation) {
            $relations[] = [
                "officeId" => $relation->provider->id,
                "serviceId" => $relation->request->id,
                "slots" => intval($relation->slots)
            ];
        }
        return $relations;
    }

    private function getScopeForProvider($sources, $providerId)
    {
        $scopeList = $sources->getScopeList();
        foreach ($scopeList as $scope) {
            if ($scope->provider->id === $providerId) {
                return [
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
        }
        return null;
    }


    public function validateServiceLocationCombination($officeId, array $serviceIds)
    {


        $availableServices = $this->getServicesProvidedAtOffice($officeId);
        $availableServiceIds = array_map(function ($service) {
            return $service['id'];
        }, $availableServices);

        $invalidServiceIds = array_filter($serviceIds, function ($serviceId) use ($availableServiceIds) {
            return !in_array($serviceId, $availableServiceIds);
        });

        if (!empty($invalidServiceIds)) {
            return [
                'status' => 400,
                'errorCode' => 'invalidLocationAndServiceCombination',
                'errorMessage' => 'The provided service(s) do not exist at the given location.',
                'invalidServiceIds' => $invalidServiceIds,
                'locationId' => $officeId,
                'lastModified' => time() * 1000,
            ];
        }

        return [
            'status' => 200,
            'message' => 'Valid service-location combination.',
        ];
    }

    public function getServicesProvidedAtOffice($officeId)
    {
        // Fetch the request relation list (assuming it's of type RequestRelationList)
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();
        
        $requestRelationList = $sources->getRequestRelationList();
        
        // Manually iterate over the RequestRelationList to convert it to an array
        $requestRelationArray = [];
        foreach ($requestRelationList as $relation) {
            $requestRelationArray[] = $relation;
        }
        
        // Now apply array_filter to the array we built
        $serviceIds = array_filter($requestRelationArray, function ($relation) use ($officeId) {
            return $relation->provider->id === $officeId;
        });
        
        $serviceIds = array_map(function ($relation) {
            return $relation->request->id;
        }, $serviceIds);
        
        // Manually iterate over the RequestList to convert it to an array
        $requestList = $sources->getRequestList();
        $requestArray = [];
        foreach ($requestList as $request) {
            $requestArray[] = $request;
        }
        
        // Return the filtered request array
        return array_filter($requestArray, function ($request) use ($serviceIds) {
            return in_array($request->id, $serviceIds);
        });
    }
    
    

}
