<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\Combinable;
use BO\Zmscitizenapi\Models\OfficeServiceRelation;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\ThinnedContact;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedProvider;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
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

class MapperService
{

    public static function mapScopeForProvider(int $providerId, ?ThinnedScopeList $scopes): ThinnedScope
    {
        if (!$scopes) {
            return new ThinnedScope();
        }
    
        $matchingScope = new ThinnedScope();
        foreach ($scopes->getScopes() as $scope) {
            if ($scope->provider && $scope->provider->id === $providerId) {
                $matchingScope = $scope;
                break;
            }
        }
    
        return $matchingScope;
    }

    public static function mapOfficesWithScope(ProviderList $providerList): OfficeList
    {
        $offices = [];
        $scopes = ZmsApiFacadeService::getScopes();
        
        if (!$scopes instanceof ThinnedScopeList) {
            return new OfficeList();
        }
    
        foreach ($providerList as $provider) {
            $providerScope = self::mapScopeForProvider((int) $provider->id, $scopes);
    
            $offices[] = new Office(
                id: isset($provider->id) ? (int) $provider->id : 0,
                name: isset($provider->displayName) ? $provider->displayName : (isset($provider->name) ? $provider->name : null),
                showAlternativeLocations: isset($provider->data['showAlternativeLocations']) ? $provider->data['showAlternativeLocations'] : null,
                address: isset($provider->data['address']) ? $provider->data['address'] : null,
                geo: isset($provider->data['geo']) ? $provider->data['geo'] : null,
                scope: isset($providerScope) && !isset($providerScope['errors']) ? new ThinnedScope(
                    id: isset($providerScope->id) ? (int) $providerScope->id : 0,
                    provider: isset($providerScope->provider) ? $providerScope->provider : null,
                    shortName: isset($providerScope->shortName) ? $providerScope->shortName : null,
                    telephoneActivated: isset($providerScope->telephoneActivated) ? (bool) $providerScope->telephoneActivated : null,
                    telephoneRequired: isset($providerScope->telephoneRequired) ? (bool) $providerScope->telephoneRequired : null,
                    customTextfieldActivated: isset($providerScope->customTextfieldActivated) ? (bool) $providerScope->customTextfieldActivated : null,
                    customTextfieldRequired: isset($providerScope->customTextfieldRequired) ? (bool) $providerScope->customTextfieldRequired : null,
                    customTextfieldLabel: isset($providerScope->customTextfieldLabel) ? $providerScope->customTextfieldLabel : null,
                    captchaActivatedRequired: isset($providerScope->captchaActivatedRequired) ? (bool) $providerScope->captchaActivatedRequired : null,
                    displayInfo: isset($providerScope->displayInfo) ? $providerScope->displayInfo : null
                ) : null
            );            
        }
    
        return new OfficeList($offices);
    }

    public static function mapCombinable(array $serviceCombinations): ?Combinable
    {
        return !empty($serviceCombinations) ? new Combinable($serviceCombinations) : null;
    }

    /**
     * Map services with combinations based on request and relation lists.
     *
     * @param RequestList $requestList
     * @param RequestRelationList $relationList
     * @return ServiceList
     */
    public static function mapServicesWithCombinations(RequestList $requestList, RequestRelationList $relationList): ServiceList
    {
        /** @var array<string, array<int>> $servicesProviderIds */
        $servicesProviderIds = [];

        foreach ($relationList as $relation) {
            $serviceId = $relation->request->id;
            $servicesProviderIds[$serviceId] ??= [];
            $servicesProviderIds[$serviceId][] = $relation->provider->id;
        }
    
        /** @var Service[] $services */
        $services = [];

        $requestArray = iterator_to_array($requestList);
        usort($requestArray, function ($a, $b) {
            return $a->getId() <=> $b->getId();  // Sorting by service ID (ascending order)
        });
    
        foreach ($requestArray as $service) {
    
            /** @var array<string, array<int>> $serviceCombinations */
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
                id: (int) $service->getId(),
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
                officeId: (int) $relation->provider->id,
                serviceId: (int) $relation->request->id,
                slots: intval($relation->slots)
            );
        }

        return new OfficeServiceRelationList($relations);
    }

    public static function scopeToThinnedScope(Scope $scope): ThinnedScope
    {
        if (!$scope || !isset($scope->id)) {
            return new ThinnedScope();
        }
    
        $thinnedProvider = null;
        try {
            if (isset($scope->provider)) {
                $provider = $scope->provider;
                $contact = $provider->contact ?? null;
                $thinnedProvider = new ThinnedProvider(
                    id: isset($provider->id) ? (int) $provider->id : null,
                    name: $provider->name ?? null,
                    source: $provider->source ?? null,
                    contact: $contact ? self::contactToThinnedContact($contact) : null
                );
            }
        } catch (\BO\Zmsentities\Exception\ScopeMissingProvider $e) {
            $thinnedProvider = null;
        }
    
        return new ThinnedScope(
            id: (int) ($scope->id ?? 0),
            provider: $thinnedProvider,
            shortName: $scope->shortName ?? null,
            telephoneActivated: isset($scope->data['telephoneActivated']) ? (bool) $scope->data['telephoneActivated'] : null,
            telephoneRequired: isset($scope->data['telephoneRequired']) ? (bool) $scope->data['telephoneRequired'] : null,
            customTextfieldActivated: isset($scope->data['customTextfieldActivated']) ? (bool) $scope->data['customTextfieldActivated'] : null,
            customTextfieldRequired: isset($scope->data['customTextfieldRequired']) ? (bool) $scope->data['customTextfieldRequired'] : null,
            customTextfieldLabel: $scope->data['customTextfieldLabel'] ?? null,
            captchaActivatedRequired: isset($scope->data['captchaActivatedRequired']) ? (bool) $scope->data['captchaActivatedRequired'] : null,
            displayInfo: $scope->data['displayInfo'] ?? null
        );
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
            processId: isset($myProcess->id) ? (int) $myProcess->id : 0,
            timestamp: (isset($myProcess->appointments[0]) && isset($myProcess->appointments[0]->date)) ? strval($myProcess->appointments[0]->date) : null,
            authKey: isset($myProcess->authKey) ? $myProcess->authKey : null,
            familyName: (isset($myProcess->clients[0]) && isset($myProcess->clients[0]->familyName)) ? $myProcess->clients[0]->familyName : null,
            customTextfield: isset($myProcess->customTextfield) ? $myProcess->customTextfield : null,
            email: (isset($myProcess->clients[0]) && isset($myProcess->clients[0]->email)) ? $myProcess->clients[0]->email : null,
            telephone: (isset($myProcess->clients[0]) && isset($myProcess->clients[0]->telephone)) ? $myProcess->clients[0]->telephone : null,
            officeName: (isset($myProcess->scope->contact) && isset($myProcess->scope->contact->name)) ? $myProcess->scope->contact->name : null,
            officeId: (isset($myProcess->scope->provider) && isset($myProcess->scope->provider->id)) ? (int) $myProcess->scope->provider->id : 0,
            scope: isset($myProcess->scope) ? self::scopeToThinnedScope($myProcess->scope) : null,
            subRequestCounts: isset($subRequestCounts) ? array_values($subRequestCounts) : [],
            serviceId: isset($mainServiceId) ? (int) $mainServiceId : 0,
            serviceCount: isset($mainServiceCount) ? $mainServiceCount : 0,
            status: (isset($myProcess->queue) && isset($myProcess->queue->status)) ? $myProcess->queue->status : null
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
        $processEntity->customTextfield = $thinnedProcess->customTextfield ?? null; // Moved to Process level
    
        $client = new Client();
        $client->familyName = $thinnedProcess->familyName ?? null;
        $client->email = $thinnedProcess->email ?? null;
        $client->telephone = $thinnedProcess->telephone ?? null;
    
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
    
        if (isset($thinnedProcess->status)) {
            $processEntity->queue = new \stdClass();
            $processEntity->queue->status = $thinnedProcess->status;
            $processEntity->status = $thinnedProcess->status;
        }
    
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
     * Converts a raw or existing contact object/array into a ThinnedContact model.
     *
     * @param object|array $contact
     * @return ThinnedContact
     */
    public static function contactToThinnedContact($contact): ThinnedContact
    {
        if (is_array($contact)) {
            return new ThinnedContact(
                city: $contact['city'] ?? null,
                country: $contact['country'] ?? null,
                name: $contact['name'] ?? null,
                postalCode: $contact['postalCode'] ?? null,
                region: $contact['region'] ?? null,
                street: $contact['street'] ?? null,
                streetNumber: $contact['streetNumber'] ?? null
            );
        }
    
        return new ThinnedContact(
            city: $contact->city ?? null,
            country: $contact->country ?? null,
            name: $contact->name ?? null,
            postalCode: $contact->postalCode ?? null,
            region: $contact->region ?? null,
            street: $contact->street ?? null,
            streetNumber: $contact->streetNumber ?? null
        );
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
            id: isset($provider->id) ? (int) $provider->id : null,
            name: isset($provider->name) ? $provider->name : null,
            source: isset($provider->source) ? $provider->source : null,
            lon: isset($provider->data['geo']['lon']) ? (float)$provider->data['geo']['lon'] : null,
            lat: isset($provider->data['geo']['lat']) ? (float)$provider->data['geo']['lat'] : null,
            contact: isset($provider->contact) ? self::contactToThinnedContact($provider->contact) : null,
        );
    }

}
