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

    public static function mapOfficesWithScope(ProviderList $providerList): array
    {
        $offices = [];

        $scopes = new ScopeList(ZmsApiClientService::getScopes() ?? []);

        foreach ($providerList as $provider) {
            $officeData = [
                "id" => $provider->id,
                "name" => $provider->displayName ?? $provider->name,
            ];
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