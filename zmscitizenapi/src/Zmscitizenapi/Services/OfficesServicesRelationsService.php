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
}
