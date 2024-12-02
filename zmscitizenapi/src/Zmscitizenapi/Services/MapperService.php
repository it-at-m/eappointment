<?php

namespace BO\Zmscitizenapi\Services;

use \BO\Zmsentities\Scope;
use \BO\Zmsentities\Collection\ProviderList;
use \BO\Zmsentities\Collection\RequestList;
use \BO\Zmsentities\Collection\RequestRelationList;
use \BO\Zmsentities\Collection\ScopeList;

class MapperService
{

    public static function mapScope(Scope $scope): array
    {
        return [
            'id' => $scope->id ?? null,
            "provider" => $scope->getProvider() ?? null,
            "shortName" => $scope->getShortName() ?? null,
            'telephoneActivated' => $scope->getTelephoneActivated() ?? null,
            'telephoneRequired' => $scope->getTelephoneRequired() ?? null,
            'customTextfieldActivated' => $scope->getCustomTextfieldActivated() ?? null,
            'customTextfieldRequired' => $scope->getCustomTextfieldRequired() ?? null,
            'customTextfieldLabel' => $scope->getCustomTextfieldLabel() ?? null,
            'captchaActivatedRequired' => $scope->getCaptchaActivatedRequired() ?? null,
            'displayInfo' => $scope->getDisplayInfo() ?? null,
        ];
    }

    public static function mapOfficesWithScope(ProviderList $providerList): array
    {
        $offices = [];

        $scopes = new ScopeList(ZmsApiClientService::getScopes() ?? []);

        foreach ($providerList as $provider) {
            $officeData = array_merge(
                [
                    "id" => $provider->id,
                    "name" => $provider->displayName ?? $provider->name,
                ],
                !empty($provider->data['address']) ? ["address" => $provider->data['address']] : [],
                !empty($provider->data['geo']) ? ["geo" => $provider->data['geo']] : []
            );  
                      
            $providerScope = ZmsApiFacadeService::getScopeForProvider($provider->id, $scopes);
            if (isset($providerScope['scope'])) {
                $officeData['scope'] = $providerScope['scope'];
            }

            $offices[] = $officeData;
        }
        return $offices;
    }

    public static function mapServicesWithCombinations(RequestList $requestList, RequestRelationList $relationList): array
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

    public static function mapRelations(RequestRelationList $relationList): array
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

}