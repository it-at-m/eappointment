<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

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
use BO\Zmscitizenapi\Utils\ClientIpHelper;
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

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @TODO: Extract class has ExcessiveClassComplexity 101 vs 100
 */
class MapperService
{
    public static function mapScopeForProvider(
        int $providerId,
        ThinnedScopeList $scopes,
        ?string $providerSource = null
    ): ?ThinnedScope {
        foreach ($scopes->getScopes() as $scope) {
            if (!$scope instanceof ThinnedScope) {
                continue;
            }

            $prov = $scope->provider ?? null;
            if (!$prov) {
                continue;
            }

            $provId  = is_object($prov) ? ($prov->id   ?? null) : ($prov['id']    ?? null);
            $provSrc = is_object($prov) ? ($prov->source ?? null) : ($prov['source'] ?? null);

            if ((string)$provId !== (string)$providerId) {
                continue;
            }

            if ($providerSource === null || $providerSource === '') {
                return $scope;
            }

            if ((string)$provSrc === (string)$providerSource) {
                return $scope;
            }
        }

        return null;
    }

    public static function extractReservationDuration(Scope|ThinnedScope|null $scope): ?int
    {
        if ($scope === null) {
            return null;
        }
        if ($scope instanceof ThinnedScope) {
            $reservationDuration = $scope->getReservationDuration();
            return $reservationDuration !== null ? (int) $reservationDuration : null;
        }
        $reservationDuration = $scope?->toProperty()?->preferences?->appointment?->reservationDuration?->get();
        return $reservationDuration !== null ? (int) $reservationDuration : null;
    }

    public static function extractActivationDuration(Scope|ThinnedScope|null $scope): ?int
    {
        if ($scope === null) {
            return null;
        }

        if ($scope instanceof ThinnedScope) {
            $activationDuration = $scope->getActivationDuration();
            if ($activationDuration === null || $activationDuration === '') {
                return null;
            }
            return (int) $activationDuration;
        }

        $activationDuration = $scope?->toProperty()?->preferences?->appointment?->activationDuration?->get();
        if ($activationDuration === null || $activationDuration === '') {
            return null;
        }
        return (int) $activationDuration;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @TODO: Extract mapping logic into specialized mapper classes for each entity type
     *
     */
    public static function mapOfficesWithScope(ProviderList $providerList, bool $showUnpublished = false): OfficeList
    {
        $offices = [];
        $scopes = ZmsApiFacadeService::getScopes();
        if (!$scopes instanceof ThinnedScopeList) {
            return new OfficeList();
        }

        foreach ($providerList as $provider) {
            // ✅ Source normalisieren: leerer String -> Fallback auf App::$source_name
            $providerSource = isset($provider->source) && $provider->source !== ''
                ? (string)$provider->source
                : (string)\App::$source_name;

            $providerScope = self::mapScopeForProvider(
                (int) $provider->id,
                $scopes,
                $providerSource
            );

            if (!$showUnpublished && isset($provider->data['public']) && !(bool) $provider->data['public']) {
                continue;
            }

            $offices[] = new Office(
                id: isset($provider->id) ? (int) $provider->id : 0,
                name: isset($provider->displayName) ? $provider->displayName : (isset($provider->name) ? $provider->name : null),
                address: isset($provider->data['address']) ? $provider->data['address'] : null,
                showAlternativeLocations: isset($provider->data['showAlternativeLocations']) ? $provider->data['showAlternativeLocations'] : null,
                displayNameAlternatives: $provider->data['displayNameAlternatives'] ?? [],
                organization: $provider->data['organization'] ?? null,
                organizationUnit: $provider->data['organizationUnit'] ?? null,
                slotTimeInMinutes: $provider->data['slotTimeInMinutes'] ?? null,
                geo: isset($provider->data['geo']) ? $provider->data['geo'] : null,
                disabledByServices: isset($provider->data['dontShowByServices']) ? $provider->data['dontShowByServices'] : [],
                priority: isset($provider->data['prio']) ? $provider->data['prio'] : 1,
                scope: isset($providerScope) && !isset($providerScope['errors']) ? new ThinnedScope(
                    id: isset($providerScope->id) ? (int) $providerScope->id : 0,
                    provider: isset($providerScope->provider) ? $providerScope->provider : null,
                    shortName: isset($providerScope->shortName) ? (string) $providerScope->shortName : null,
                    emailFrom: isset($providerScope->emailFrom) ? (string) $providerScope->emailFrom : null,
                    emailRequired: isset($providerScope->emailRequired) ? (bool) $providerScope->emailRequired : null,
                    telephoneActivated: isset($providerScope->telephoneActivated) ? (bool) $providerScope->telephoneActivated : null,
                    telephoneRequired: isset($providerScope->telephoneRequired) ? (bool) $providerScope->telephoneRequired : null,
                    customTextfieldActivated: isset($providerScope->customTextfieldActivated) ? (bool) $providerScope->customTextfieldActivated : null,
                    customTextfieldRequired: isset($providerScope->customTextfieldRequired) ? (bool) $providerScope->customTextfieldRequired : null,
                    customTextfieldLabel: isset($providerScope->customTextfieldLabel) ? (string) $providerScope->customTextfieldLabel : null,
                    customTextfield2Activated: isset($providerScope->customTextfield2Activated) ? (bool) $providerScope->customTextfield2Activated : null,
                    customTextfield2Required: isset($providerScope->customTextfield2Required) ? (bool) $providerScope->customTextfield2Required : null,
                    customTextfield2Label: isset($providerScope->customTextfield2Label) ? (string) $providerScope->customTextfield2Label : null,
                    captchaActivatedRequired: isset($providerScope->captchaActivatedRequired) ? (bool) $providerScope->captchaActivatedRequired : null,
                    infoForAppointment: isset($providerScope->infoForAppointment)
                        ? ((string) $providerScope->infoForAppointment === '' ? null : (string) $providerScope->infoForAppointment)
                        : null,
                    infoForAllAppointments: isset($providerScope->infoForAllAppointments)
                        ? ((string) $providerScope->infoForAllAppointments === '' ? null : (string) $providerScope->infoForAllAppointments)
                        : null,
                    appointmentsPerMail: isset($providerScope->appointmentsPerMail) ? ((string) $providerScope->appointmentsPerMail === '' ? null : (string) $providerScope->appointmentsPerMail) : null,
                    whitelistedMails: isset($providerScope->whitelistedMails) ? ((string) $providerScope->whitelistedMails === '' ? null : (string) $providerScope->whitelistedMails) : null,
                    reservationDuration: (int) self::extractReservationDuration($providerScope),
                    activationDuration: self::extractActivationDuration($providerScope),
                    hint: isset($providerScope->hint) ? (trim((string) $providerScope->hint) === '' ? null : (string) $providerScope->hint) : null
                ) : null,
                maxSlotsPerAppointment: isset($providerScope) && !isset($providerScope['errors']) && isset($providerScope->slotsPerAppointment) ? ((string) $providerScope->slotsPerAppointment === '' ? null : (string) $providerScope->slotsPerAppointment) : null,
                parentId: isset($provider->parent_id) ? (int) $provider->parent_id : null
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function mapServicesWithCombinations(
        RequestList $requestList,
        RequestRelationList $relationList,
        bool $showUnpublished = false
    ): ServiceList {
        /** @var array<string, array<int>> $servicesProviderIds */
        $servicesProviderIds = [];
        foreach ($relationList as $relation) {
            if (!$showUnpublished && !$relation->isPublic()) {
                continue;
            }

            $serviceId = $relation->request->id;
            $servicesProviderIds[$serviceId] ??= [];
            $servicesProviderIds[$serviceId][] = $relation->provider->id;
        }

        /** @var Service[] $services */
        $services = [];
        $requestArray = iterator_to_array($requestList);
        usort($requestArray, function ($a, $b) {

            return $a->getId() <=> $b->getId();
            // Sorting by service ID (ascending order)
        });
        foreach ($requestArray as $service) {
            if (
                !$showUnpublished
                && isset($service->getAdditionalData()['public'])
                && !$service->getAdditionalData()['public']
            ) {
                continue;
            }

            /** @var array<string, array<int>> $serviceCombinations */
            $serviceCombinations = [];
            $combinableData = $service->getAdditionalData()['combinable'] ?? [];
            foreach ($combinableData as $combinationServiceId) {
                $commonProviders = array_intersect($servicesProviderIds[$service->getId()] ?? [], $servicesProviderIds[$combinationServiceId] ?? []);
                $serviceCombinations[$combinationServiceId] = !empty($commonProviders) ? array_values($commonProviders) : [];
            }

            $combinable = self::mapCombinable($serviceCombinations);

            $extra = $service->getAdditionalData() ?? [];
            $parentId  = isset($service->parent_id)  ? (int)$service->parent_id  : (isset($extra['parent_id'])  ? (int)$extra['parent_id']  : null);
            $variantId = isset($service->variant_id) ? (int)$service->variant_id : (isset($extra['variant_id']) ? (int)$extra['variant_id'] : null);

            if (!empty($servicesProviderIds[$service->getId()])) {
                $services[] = new Service(id: (int) $service->getId(), name: $service->getName(), maxQuantity: $service->getAdditionalData()['maxQuantity'] ?? 1, combinable: $combinable ?? new Combinable(), parentId: $parentId, variantId: $variantId);
            }
        }

        return new ServiceList($services);
    }


    public static function mapRelations(
        RequestRelationList $relationList,
        bool $showUnpublished = false
    ): OfficeServiceRelationList {
        $relations = [];
        foreach ($relationList as $relation) {
            if (!$showUnpublished && !$relation->isPublic()) {
                continue;
            }

            $relations[] = new OfficeServiceRelation(
                officeId: (int) $relation->provider->id,
                serviceId: (int) $relation->request->id,
                slots: intval($relation->slots),
                public: (bool) $relation->isPublic(),
                maxQuantity: (int) $relation->maxQuantity
            );
        }

        return new OfficeServiceRelationList($relations);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
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
                    id: isset($provider->id) ? (int)$provider->id : null,
                    name: $provider->name ?? null,
                    displayName: $provider->displayName ?? null,
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
            shortName: isset($scope->shortName) ? (string) $scope->shortName : null,
            emailFrom: (string) $scope->getEmailFrom() ?? null,
            emailRequired: isset($scope->data['emailRequired']) ? (bool) $scope->data['emailRequired'] : null,
            telephoneActivated: isset($scope->data['telephoneActivated']) ? (bool) $scope->data['telephoneActivated'] : null,
            telephoneRequired: isset($scope->data['telephoneRequired']) ? (bool) $scope->data['telephoneRequired'] : null,
            customTextfieldActivated: isset($scope->data['customTextfieldActivated']) ? (bool) $scope->data['customTextfieldActivated'] : null,
            customTextfieldRequired: isset($scope->data['customTextfieldRequired']) ? (bool) $scope->data['customTextfieldRequired'] : null,
            customTextfieldLabel: isset($scope->data['customTextfieldLabel']) ? (string) $scope->data['customTextfieldLabel'] : null,
            customTextfield2Activated: isset($scope->data['customTextfield2Activated']) ? (bool) $scope->data['customTextfield2Activated'] : null,
            customTextfield2Required: isset($scope->data['customTextfield2Required']) ? (bool) $scope->data['customTextfield2Required'] : null,
            customTextfield2Label: isset($scope->data['customTextfield2Label']) ? (string) $scope->data['customTextfield2Label'] : null,
            captchaActivatedRequired: isset($scope->data['captchaActivatedRequired']) ? (bool) $scope->data['captchaActivatedRequired'] : null,
            infoForAppointment: isset($scope->data['infoForAppointment'])
                ? ((string) $scope->data['infoForAppointment'] === ''
                    ? null
                    : (string) $scope->data['infoForAppointment'])
                : null,
            infoForAllAppointments: isset($scope->data['infoForAllAppointments'])
                ? ((string) $scope->data['infoForAllAppointments'] === ''
                    ? null
                    : (string) $scope->data['infoForAllAppointments'])
                : null,
            slotsPerAppointment: isset($scope->data['slotsPerAppointment'])
                ? ((string) $scope->data['slotsPerAppointment'] === ''
                    ? null
                    : (string) $scope->data['slotsPerAppointment'])
                : null,
            appointmentsPerMail: isset($scope->data['appointmentsPerMail']) ? ((string) $scope->data['appointmentsPerMail'] === '' ? null : (string) $scope->data['appointmentsPerMail']) : null,
            whitelistedMails: isset($scope->data['whitelistedMails']) ? ((string) $scope->data['whitelistedMails'] === '' ? null : (string) $scope->data['whitelistedMails']) : null,
            reservationDuration: MapperService::extractReservationDuration($scope),
            activationDuration: MapperService::extractActivationDuration($scope),
            hint: (trim((string) ($scope->getScopeHint() ?? '')) === '') ? null : (string) $scope->getScopeHint()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @TODO: Break down process mapping into smaller, focused methods
     */
    public static function processToThinnedProcess(Process $myProcess): ThinnedProcess
    {
        if (!$myProcess || !isset($myProcess->id)) {
            return new ThinnedProcess();
        }

        $subRequestCounts = [];
        $mainServiceId = null;
        $mainServiceName = null;
        $mainServiceCount = 0;
        $requests = $myProcess->getRequests() ?? [];
        if ($requests) {
            $requests = is_array($requests) ? $requests : iterator_to_array($requests);
            if (count($requests) > 0) {
                $mainServiceId = $requests[0]->id;
                foreach ($requests as $request) {
                    if ($request->id === $mainServiceId) {
                        $mainServiceCount++;
                        if (!$mainServiceName && isset($request->name)) {
                            $mainServiceName = $request->name;
                        }
                    } else {
                        if (!isset($subRequestCounts[$request->id])) {
                            $subRequestCounts[$request->id] = [
                                'id' => (int) $request->id,
                                'name'  => $request->name,
                                'count' => 0,
                            ];
                        }
                        $subRequestCounts[$request->id]['count']++;
                    }
                }
            }
        }

        // Generate ICS content if process has appointments with time
        $icsContent = self::generateIcsContent($myProcess);

        return new ThinnedProcess(
            processId: isset($myProcess->id) ? (int) $myProcess->id : 0,
            timestamp: (isset($myProcess->appointments[0]) && isset($myProcess->appointments[0]->date)) ? strval($myProcess->appointments[0]->date) : null,
            authKey: isset($myProcess->authKey) ? $myProcess->authKey : null,
            captchaToken: isset($myProcess->captchaToken) ? $myProcess->captchaToken : null,
            familyName: (isset($myProcess->clients[0]) && isset($myProcess->clients[0]->familyName)) ? $myProcess->clients[0]->familyName : null,
            customTextfield: isset($myProcess->customTextfield) ? $myProcess->customTextfield : null,
            customTextfield2: isset($myProcess->customTextfield2) ? $myProcess->customTextfield2 : null,
            email: (isset($myProcess->clients[0]) && isset($myProcess->clients[0]->email)) ? $myProcess->clients[0]->email : null,
            telephone: (isset($myProcess->clients[0]) && isset($myProcess->clients[0]->telephone)) ? $myProcess->clients[0]->telephone : null,
            officeName: (isset($myProcess->scope->contact) && isset($myProcess->scope->contact->name)) ? $myProcess->scope->contact->name : null,
            officeId: (isset($myProcess->scope->provider) && isset($myProcess->scope->provider->id)) ? (int) $myProcess->scope->provider->id : 0,
            scope: isset($myProcess->scope) ? self::scopeToThinnedScope($myProcess->scope) : null,
            subRequestCounts: isset($subRequestCounts) ? array_values($subRequestCounts) : [],
            serviceId: isset($mainServiceId) ? (int) $mainServiceId : 0,
            serviceName: isset($mainServiceName) ? $mainServiceName : null,
            serviceCount: isset($mainServiceCount) ? $mainServiceCount : 0,
            status: (isset($myProcess->queue) && isset($myProcess->queue->status)) ? $myProcess->queue->status : null,
            slotCount: (isset($myProcess->appointments[0]) && isset($myProcess->appointments[0]->slotCount)) ? (int) $myProcess->appointments[0]->slotCount : null,
            icsContent: isset($icsContent) ? $icsContent : null
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
        $processEntity->customTextfield = $thinnedProcess->customTextfield ?? null;
        $processEntity->customTextfield2 = $thinnedProcess->customTextfield2 ?? null;
        $processEntity->captchaToken = $thinnedProcess->captchaToken ?? null;

        $client = new Client();
        $client->familyName = $thinnedProcess->familyName ?? null;
        $client->email = $thinnedProcess->email ?? null;
        $client->telephone = $thinnedProcess->telephone ?? null;
        $processEntity->clients = [$client];

        $appointment = new Appointment();
        $appointment->slotCount = $thinnedProcess->slotCount ?? null;
        $appointment->date = $thinnedProcess->timestamp ?? null;
        $processEntity->appointments = [$appointment];
        $processEntity->scope = self::createScope($thinnedProcess);
        $processEntity->requests = self::createRequests($thinnedProcess);

        if (isset($thinnedProcess->status)) {
            $processEntity->queue = new \stdClass();
            $processEntity->queue->status = $thinnedProcess->status;
            $processEntity->status = $thinnedProcess->status;
        }

        $processEntity->lastChange = time();
        $processEntity->createIP = ClientIpHelper::getClientIp();
        $processEntity->createTimestamp = time();
        return $processEntity;
    }

    private static function createScope(ThinnedProcess $thinnedProcess): Scope
    {
        $scope = new Scope();
        if ($thinnedProcess->scope) {
            $providerSource = $thinnedProcess->scope->provider->source ?? 'dldb';

            $scope->id = $thinnedProcess->scope->id;
            $scope->source = $providerSource;

            $scope->preferences = [
                'client' => [
                    'appointmentsPerMail' => $thinnedProcess->scope->getAppointmentsPerMail() ?? null,
                    "whitelistedMails" => $thinnedProcess->scope->getWhitelistedMails() ?? null,
                    'emailFrom' => $thinnedProcess->scope->getEmailFrom() ?? null,
                    'emailRequired' => $thinnedProcess->scope->getEmailRequired() ?? false,
                    'telephoneActivated' => $thinnedProcess->scope->getTelephoneActivated() ?? false,
                    'telephoneRequired' => $thinnedProcess->scope->getTelephoneRequired() ?? false,
                    'customTextfieldActivated' => $thinnedProcess->scope->getCustomTextfieldActivated() ?? false,
                    'customTextfieldRequired' => $thinnedProcess->scope->getCustomTextfieldRequired() ?? false,
                    'customTextfieldLabel' => $thinnedProcess->scope->getCustomTextfieldLabel() ?? null,
                    'customTextfield2Activated' => $thinnedProcess->scope->getCustomTextfield2Activated() ?? false,
                    'customTextfield2Required' => $thinnedProcess->scope->getCustomTextfield2Required() ?? false,
                    'customTextfield2Label' => $thinnedProcess->scope->getCustomTextfield2Label() ?? null
                ],
                'notifications' => [
                    'enabled' => true
                ]
            ];
        }
        if (isset($thinnedProcess->officeName)) {
            $scope->contact = new Contact();
            $scope->contact->name = $thinnedProcess->officeName;
        }
        if (isset($thinnedProcess->officeId)) {
            $scope->provider = new Provider();
            $scope->provider->id = $thinnedProcess->officeId;
            if (isset($thinnedProcess->scope->provider)) {
                $provider = $thinnedProcess->scope->provider;
                $scope->provider->name  = $provider->name ?? null;
                $scope->provider->displayName = $provider->displayName ?? null;

                if (isset($provider->contact)) {
                    $scope->provider->contact = new Contact();
                    $scope->provider->contact->street = $provider->contact->street ?? null;
                    $scope->provider->contact->streetNumber = $provider->contact->streetNumber ?? null;
                }
            }

            $scope->provider->source = $thinnedProcess->scope->provider->source ?? null;
        }

        return $scope;
    }

    private static function createRequests(ThinnedProcess $thinnedProcess): array
    {
        $providerSource = $thinnedProcess->scope->provider->source ?? 'dldb';

        $requests = [];
        $mainServiceId = $thinnedProcess->serviceId ?? null;
        $mainServiceName = $thinnedProcess->serviceName ?? null;
        $mainServiceCount = $thinnedProcess->serviceCount ?? 0;

        for ($i = 0; $i < $mainServiceCount; $i++) {
            $request = new Request();
            $request->id = $mainServiceId;
            $request->name = $mainServiceName;
            $request->source = $providerSource;
            $requests[] = $request;
        }

        foreach ($thinnedProcess->subRequestCounts ?? [] as $subRequest) {
            $subId = $subRequest['id'] ?? null;
            $subName = $subRequest['name'] ?? null;
            $count = (int)($subRequest['count'] ?? 0);

            for ($i = 0; $i < $count; $i++) {
                $request = new Request();
                $request->id = $subId;
                $request->name = $subName;
                $request->source = $providerSource;
                $requests[] = $request;
            }
        }

        return $requests;
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
                postalCode: isset($contact['postalCode']) ? (is_null($contact['postalCode']) ? null : (string)$contact['postalCode']) : null,
                region: $contact['region'] ?? null,
                street: $contact['street'] ?? null,
                streetNumber: $contact['streetNumber'] ?? null
            );
        }

        return new ThinnedContact(
            city: $contact->city ?? null,
            country: $contact->country ?? null,
            name: $contact->name ?? null,
            postalCode: isset($contact->postalCode) ? (is_null($contact->postalCode) ? null : (string)$contact->postalCode) : null,
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
            displayName: isset($provider->displayName) ? $provider->displayName : null,
            source: isset($provider->source) ? $provider->source : null,
            lon: isset($provider->data['geo']['lon']) ? (float) $provider->data['geo']['lon'] : null,
            lat: isset($provider->data['geo']['lat']) ? (float) $provider->data['geo']['lat'] : null,
            contact: isset($provider->contact) ? self::contactToThinnedContact($provider->contact) : null
        );
    }

    /**
     * Generate ICS content for a process if it has appointments with time.
     *
     * @param Process $process The process to generate ICS content for
     * @return string|null The ICS content or null if generation fails or not applicable
     */
    private static function generateIcsContent(Process $process): ?string
    {
        if (!isset($process->appointments[0]) || !$process->appointments[0]->hasTime()) {
            return null;
        }

        $content = ZmsApiClientService::getIcsContent((int)($process->id ?? 0), (string)($process->authKey ?? ''));
        return $content ?: null;
    }
}
