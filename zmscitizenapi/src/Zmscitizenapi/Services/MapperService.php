<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmsentities\Appointment;
use BO\Zmsentities\Client;
use BO\Zmsentities\Contact;
use BO\Zmsentities\Process;
use BO\Zmsentities\Provider;
use BO\Zmsentities\Request;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Collection\ScopeList;

class MapperService
{

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
            if (isset($providerScope) && !isset($providerScope['errors'])) {
                $officeData['scope'] = $providerScope;
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

    public static function scopeToThinnedScope(Scope $myscope): ThinnedScope
    {
        if (!$myscope || !isset($myscope->id)) {
            return new ThinnedScope();
        }

        $provider = null;

        if (isset($myscope->provider)) {
            $provider = new Provider();
            $provider->id = $myscope->provider->id ?? null;
            $provider->name = $myscope->provider->name ?? null;
            $provider->link = $myscope->provider->link ?? null;
            $provider->source = $myscope->provider->source ?? null;
    
            if (isset($myscope->provider->contact)) {
                $provider->contact = new Contact();
                $provider->contact->name = $myscope->provider->contact->name ?? null;
                $provider->contact->street = $myscope->provider->contact->street ?? null;
                $provider->contact->streetNumber = $myscope->provider->contact->streetNumber ?? null;
                $provider->contact->city = $myscope->provider->contact->city ?? null;
                $provider->contact->postalCode = $myscope->provider->contact->postalCode ?? null;
                $provider->contact->region = $myscope->provider->contact->region ?? null;
                $provider->contact->country = $myscope->provider->contact->country ?? null;
            }
        }
    
        return new ThinnedScope(
            id: $myscope->id,
            provider: $provider ?? null,
            shortName: $myscope->shortName ?? null,
            telephoneActivated: $myscope->telephoneActivated ?? null,
            telephoneRequired: $myscope->telephoneRequired ?? null,
            customTextfieldActivated: $myscope->customTextfieldActivated ?? null,
            customTextfieldRequired: $myscope->customTextfieldRequired ?? null,
            customTextfieldLabel: $myscope->customTextfieldLabel ?? null,
            captchaActivatedRequired: $myscope->captchaActivatedRequired ?? null,
            displayInfo: $myscope->displayInfo ?? null
        );
    }    
    
    public static function thinnedScopeToScope(ThinnedScope $thinnedScope): Scope
    {
        if (!$thinnedScope || !isset($thinnedScope->id)) {
            return new Scope();
        }

        $scopeEntity = new Scope();
        $scopeEntity->id = $thinnedScope->id;
    
        if ($thinnedScope->provider) {
            $provider = new Provider();
            $provider->id = $thinnedScope->provider->id ?? null;
            $provider->name = $thinnedScope->provider->name ?? null;
            $scopeEntity->provider = $provider;
        }
    
        $scopeEntity->shortName = $thinnedScope->shortName ?? null;
        $scopeEntity->telephoneActivated = $thinnedScope->telephoneActivated ?? null;
        $scopeEntity->telephoneRequired = $thinnedScope->telephoneRequired ?? null;
        $scopeEntity->customTextfieldActivated = $thinnedScope->customTextfieldActivated ?? null;
        $scopeEntity->customTextfieldRequired = $thinnedScope->customTextfieldRequired ?? null;
        $scopeEntity->customTextfieldLabel = $thinnedScope->customTextfieldLabel ?? null;
        $scopeEntity->captchaActivatedRequired = $thinnedScope->captchaActivatedRequired ?? null;
        $scopeEntity->displayInfo = $thinnedScope->displayInfo ?? null;
    
        return $scopeEntity;
    }        

    public static function processToThinnedProcess(Process $myProcess): ThinnedProcess
    {
        if (!$myProcess || !isset($myProcess->id)) {
            return new ThinnedProcess();
        }

        $subRequestCounts = [];
        $mainServiceId = null;
        $mainServiceCount = 0;

        $requests = $myProcess->getRequests() ?? [];
        if ($requests) {
            $requests = is_array($requests) ? $requests : iterator_to_array($requests);
            if (count($requests) > 0) {
                $mainServiceId = $requests[0]->id;
                foreach ($requests as $request) {
                    if ($request->id === $mainServiceId) {
                        $mainServiceCount++;
                    } else {
                        if (!isset($subRequestCounts[$request->id])) {
                            $subRequestCounts[$request->id] = [
                                'id' => $request->id,
                                'count' => 0,
                            ];
                        }
                        $subRequestCounts[$request->id]['count']++;
                    }
                }
            }
        }

        return new ThinnedProcess(
            processId: intval($myProcess->id),
            timestamp: isset($myProcess->appointments[0]) ? strval($myProcess->appointments[0]->date) : null,
            authKey: $myProcess->authKey ?? null,
            familyName: isset($myProcess->clients[0]) ? $myProcess->clients[0]->familyName : null,
            customTextfield: $myProcess->customTextfield ?? null,
            email: isset($myProcess->clients[0]) ? $myProcess->clients[0]->email : null,
            telephone: isset($myProcess->clients[0]) ? $myProcess->clients[0]->telephone : null,
            officeName: $myProcess->scope->contact->name ?? null,
            officeId: intval($myProcess->scope->provider->id ?? 0),
            scope: $myProcess->scope ? self::scopeToThinnedScope($myProcess->scope) : null,
            subRequestCounts: array_values($subRequestCounts),
            serviceId: $mainServiceId,
            serviceCount: $mainServiceCount
        );
    }

    public static function thinnedProcessToProcess(ThinnedProcess $thinnedProcess): Process
    {
        if (!$thinnedProcess || !isset($thinnedProcess->processId)) {
            return new Process();
        }

        $processEntity = new Process();
        $processEntity->id = $thinnedProcess->processId;
        $processEntity->authKey = $thinnedProcess->authKey ?? null;

        $client = new Client();
        $client->familyName = $thinnedProcess->familyName ?? null;
        $client->email = $thinnedProcess->email ?? null;
        $client->telephone = $thinnedProcess->telephone ?? null;
        $client->customTextfield = $thinnedProcess->customTextfield ?? null;

        $processEntity->clients = [$client];

        $appointment = new Appointment();
        $appointment->date = $thinnedProcess->timestamp ?? null;
        $processEntity->appointments = [$appointment];

        $scope = new Scope();
        if (isset($thinnedProcess->officeName)) {
            $scope->contact = new Contact();
            $scope->contact->name = $thinnedProcess->officeName;
        }
        if (isset($thinnedProcess->officeId)) {
            $scope->provider = new Provider();
            $scope->provider->id = $thinnedProcess->officeId;
            $scope->provider->source = \App::$source_name;
        }
        $processEntity->scope = $scope;

        $mainServiceId = $thinnedProcess->serviceId ?? null;
        $mainServiceCount = $thinnedProcess->serviceCount ?? 0;
        $subRequestCounts = $thinnedProcess->subRequestCounts ?? [];

        $requests = [];
        for ($i = 0; $i < $mainServiceCount; $i++) {
            $request = new Request();
            $request->id = $mainServiceId;
            $request->source = \App::$source_name;
            $requests[] = $request;
        }
        foreach ($subRequestCounts as $subRequest) {
            for ($i = 0; $i < ($subRequest['count'] ?? 0); $i++) {
                $request = new Request();
                $request->id = $subRequest['id'];
                $request->source = \App::$source_name;
                $requests[] = $request;
            }
        }
        $processEntity->requests = $requests;

        $processEntity->lastChange = time();
        $processEntity->createIP = ClientIpHelper::getClientIp();
        $processEntity->createTimestamp = time();

        return $processEntity;
    }

}