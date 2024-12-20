<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\Combinable;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\OfficeServiceRelation;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedProvider;
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

    public static function mapOfficesWithScope(ProviderList $providerList): OfficeList
    {
        $offices = [];
        $scopes = new ScopeList(ZmsApiClientService::getScopes() ?? []);

        foreach ($providerList as $provider) {
            $providerScope = ZmsApiFacadeService::getScopeForProvider($provider->id, $scopes);

            $offices[] = new Office(
                id: (int)$provider->id,
                name: $provider->displayName ?? $provider->name,
                address: $provider->data['address'] ?? null,
                geo: $provider->data['geo'] ?? null,
                scope: isset($providerScope) && !isset($providerScope['errors']) ? new ThinnedScope(
                    id: (int)($providerScope->id ?? 0),
                    provider: $providerScope->provider ?? null,
                    shortName: $providerScope->shortName ?? null,
                    telephoneActivated: $providerScope->telephoneActivated ?? null,
                    telephoneRequired: $providerScope->telephoneRequired ?? null,
                    customTextfieldActivated: $providerScope->customTextfieldActivated ?? null,
                    customTextfieldRequired: $providerScope->customTextfieldRequired ?? null,
                    customTextfieldLabel: $providerScope->customTextfieldLabel ?? null,
                    captchaActivatedRequired: $providerScope->captchaActivatedRequired ?? null,
                    displayInfo: $providerScope->displayInfo ?? null
                ) : null
            );
        }

        return new OfficeList($offices);
    }

    public static function mapCombinable(array $serviceCombinations): ?Combinable
    {
        return !empty($serviceCombinations) ? new Combinable($serviceCombinations) : null;
    }

    public static function mapServicesWithCombinations(RequestList $requestList, RequestRelationList $relationList): ServiceList
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
            $combinableData = $service->getAdditionalData()['combinable'] ?? [];

            foreach ($combinableData as $combinationServiceId) {
                $commonProviders = array_intersect(
                    $servicesProviderIds[$service->getId()] ?? [],
                    $servicesProviderIds[$combinationServiceId] ?? []
                );
                $serviceCombinations[$combinationServiceId] = !empty($commonProviders) ? array_values($commonProviders) : [];
            }

            $combinable = self::mapCombinable($serviceCombinations);

            $services[] = new Service(
                id: (int)$service->getId(),
                name: $service->getName(),
                maxQuantity: $service->getAdditionalData()['maxQuantity'] ?? 1,
                combinable: $combinable ?? new Combinable()
            );
        }

        return new ServiceList($services);
    }


    public static function mapRelations(RequestRelationList $relationList): OfficeServiceRelationList
    {
        $relations = [];
        foreach ($relationList as $relation) {
            $relations[] = new OfficeServiceRelation(
                officeId: (string)$relation->provider->id,
                serviceId: (string)$relation->request->id,
                slots: intval($relation->slots)
            );
        }

        return new OfficeServiceRelationList($relations);
    }

    public static function scopeToThinnedScope(Scope $myscope): ThinnedScope
    {
        if (!$myscope || !isset($myscope->id)) {
            return new ThinnedScope();
        }

        $provider = null;

        if (isset($myscope->provider)) {
            // Convert Provider to ThinnedProvider
            $provider = self::providerToThinnedProvider($myscope->provider);
        }

        return new ThinnedScope(
            id: $myscope->id,
            provider: $provider,
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
            // Convert ThinnedProvider to Provider
            $scopeEntity->provider = self::thinnedProviderToProvider($thinnedScope->provider);
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

    /**
     * Convert a Provider object to a ThinnedProvider.
     *
     * @param Provider $provider
     * @return ThinnedProvider
     */
    public static function providerToThinnedProvider(Provider $provider): ThinnedProvider
    {
        return new ThinnedProvider(
            id: isset($provider->id) ? (int)$provider->id : null,
            name: $provider->name ?? null,
            source: $provider->source ?? null,
            contact: $provider->contact ?? null,
        );
    }

    /**
     * Convert a ThinnedProvider object to a Provider.
     *
     * @param ThinnedProvider $thinnedProvider
     * @return Provider
     */
    public static function thinnedProviderToProvider(ThinnedProvider $thinnedProvider): Provider
    {
        $provider = new Provider();
        $provider->id = isset($thinnedProvider->id) ? (string)$thinnedProvider->id : null; // Convert int ID to string
        $provider->name = $thinnedProvider->name ?? null;
        $provider->source = $thinnedProvider->source ?? null;

        if ($thinnedProvider->address || $thinnedProvider->geo) {
            $provider->data = [
                'address' => $thinnedProvider->address ?? null,
                'geo' => $thinnedProvider->geo ?? null
            ];
        }

        if ($thinnedProvider->telephone || $thinnedProvider->email) {
            $provider->contact = new Contact();
            $provider->contact->telephone = $thinnedProvider->telephone ?? null;
            $provider->contact->email = $thinnedProvider->email ?? null;
        }

        return $provider;
    }
}
