<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesServicesRelations extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $providerList = $sources->getProviderList() ?? [];
        $requestList = $sources->getRequestList() ?? [];
        $relationList = $sources->getRequestRelationList() ?? [];
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
        $services = $this->mapServicesWithCombinations($requestList, $relationList);
        $relations = [];
        foreach ($relationList as $relation) {
            $relations[] = [
                "officeId" => $relation->provider->id,
                "serviceId" => $relation->request->id,
                "slots" => intval($relation->slots)
            ];
        }
        return Render::withJson($response, [
            "offices" => $offices,
            "services" => $services,
            "relations" => $relations,
        ]);
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
                    if (!empty($commonProviders)) {
                        $serviceCombinations[$combinationServiceId] = array_values($commonProviders);
                    } else {
                        $serviceCombinations[$combinationServiceId] = [];
                    }
                }
                $mappedService['combinable'] = $serviceCombinations;
            }    
            $services[] = $mappedService;
        }
    
        return $services;
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
                ];
            }
        }
        return null;
    }
}
