<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\AvailableAppointmentsByOffice;
use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Models\AvailableDaysByOffice;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\Core\ZmsApiClientService;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\ProcessList;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @TODO: Break down this facade into smaller domain-specific facades or use the Command pattern
 */
class ZmsApiFacadeService
{
    private const CACHE_KEY_OFFICES = 'processed_offices';
    private const CACHE_KEY_SCOPES = 'processed_scopes';
    private const CACHE_KEY_SERVICES = 'processed_services';
    private const CACHE_KEY_OFFICES_AND_SERVICES = 'processed_offices_and_services';
    private const CACHE_KEY_OFFICES_BY_SERVICE_PREFIX = 'processed_offices_by_service_';
    private const CACHE_KEY_SERVICES_BY_OFFICE_PREFIX = 'processed_services_by_office_';

    private static ?string $currentLanguage = null;
    public static function setLanguageContext(?string $language): void
    {
        self::$currentLanguage = $language;
    }

    private static function getError(string $key): array
    {
        return ErrorMessages::get($key, self::$currentLanguage);
    }

    private static function setMappedCache(string $cacheKey, mixed $data): void
    {
        if (\App::$cache) {
            \App::$cache->set($cacheKey, $data, \App::$SOURCE_CACHE_TTL);
            LoggerService::logInfo('Second-level cache set', [
                'key' => $cacheKey,
                'ttl' => \App::$SOURCE_CACHE_TTL
            ]);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getOffices(bool $showUnpublished = false): OfficeList
    {
        $cacheKey = self::CACHE_KEY_OFFICES . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $scopeList = ZmsApiClientService::getScopes() ?? new ScopeList();
        $offices = [];
        $scopeMap = [];
        foreach ($scopeList as $scope) {
            if ($scope->getProvider()) {
                $scopeMap[$scope->getProvider()->source . '_' . $scope->getProvider()->id] = $scope;
            }
        }

        foreach ($providerList as $provider) {
            if (!$showUnpublished && isset($provider->data['public']) && !(bool) $provider->data['public']) {
                continue;
            }

            $matchingScope = $scopeMap[$provider->source . '_' . $provider->id] ?? null;
            $offices[] = new Office(
                id: (int) $provider->id,
                name: $provider->displayName ?? $provider->name,
                address: $provider->data['address'] ?? null,
                showAlternativeLocations: $provider->data['showAlternativeLocations'] ?? null,
                displayNameAlternatives: $provider->data['displayNameAlternatives'] ?? [],
                organization: $provider->data['organization'] ?? null,
                organizationUnit: $provider->data['organizationUnit'] ?? null,
                slotTimeInMinutes: $provider->data['slotTimeInMinutes'] ?? null,
                geo: $provider->data['geo'] ?? null,
                scope: $matchingScope ? new ThinnedScope(
                    id: (int) $matchingScope->id,
                    provider: MapperService::providerToThinnedProvider($provider),
                    shortName: (string) $matchingScope->getShortName(),
                    emailFrom: (string) $matchingScope->getEmailFrom(),
                    emailRequired: (bool) $matchingScope->getEmailRequired(),
                    telephoneActivated: (bool) $matchingScope->getTelephoneActivated(),
                    telephoneRequired: (bool) $matchingScope->getTelephoneRequired(),
                    customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(),
                    customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(),
                    customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(),
                    customTextfield2Activated: (bool) $matchingScope->getCustomTextfield2Activated(),
                    customTextfield2Required: (bool) $matchingScope->getCustomTextfield2Required(),
                    customTextfield2Label: $matchingScope->getCustomTextfield2Label(),
                    captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(),
                    displayInfo: $matchingScope->getDisplayInfo(),
                    slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()),
                    appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()),
                    whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails())
                ) : null,
                maxSlotsPerAppointment: $matchingScope ? ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()) : null
            );
        }

        $result = new OfficeList($offices);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    public static function getScopes(): ThinnedScopeList|array
    {
        $cacheKey = self::CACHE_KEY_SCOPES;

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $scopeList = ZmsApiClientService::getScopes() ?? new ScopeList();
        $scopeMap = [];
        foreach ($scopeList as $scope) {
            $scopeProvider = $scope->getProvider();
            if ($scopeProvider && $scopeProvider->id && $scopeProvider->source) {
                $key = $scopeProvider->source . '_' . $scopeProvider->id;
                $scopeMap[$key] = $scope;
            }
        }

        $scopesProjectionList = [];
        foreach ($providerList as $provider) {
            $key = $provider->source . '_' . $provider->id;
            if (isset($scopeMap[$key])) {
                $matchingScope = $scopeMap[$key];
                $scopesProjectionList[] = new ThinnedScope(
                    id: (int) $matchingScope->id,
                    provider: MapperService::providerToThinnedProvider($provider),
                    shortName: (string) $matchingScope->getShortName(),
                    emailFrom: (string) $matchingScope->getEmailFrom(),
                    emailRequired: (bool) $matchingScope->getEmailRequired(),
                    telephoneActivated: (bool) $matchingScope->getTelephoneActivated(),
                    telephoneRequired: (bool) $matchingScope->getTelephoneRequired(),
                    customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(),
                    customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(),
                    customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(),
                    customTextfield2Activated: (bool) $matchingScope->getCustomTextfield2Activated(),
                    customTextfield2Required: (bool) $matchingScope->getCustomTextfield2Required(),
                    customTextfield2Label: $matchingScope->getCustomTextfield2Label(),
                    captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(),
                    displayInfo: $matchingScope->getDisplayInfo(),
                    slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()),
                    appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()),
                    whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails())
                );
            }
        }

        $result = new ThinnedScopeList($scopesProjectionList);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    public static function getServices(bool $showUnpublished = false): ServiceList|array
    {
        $cacheKey = self::CACHE_KEY_SERVICES . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $requestList = ZmsApiClientService::getServices() ?? new RequestList();
        $services = [];
        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            if (
                !$showUnpublished
                && isset($additionalData['public'])
                && !$additionalData['public']
            ) {
                continue;
            }

            $services[] = new Service(id: (int) $request->getId(), name: $request->getName(), maxQuantity: $additionalData['maxQuantity'] ?? 1);
        }

        $result = new ServiceList($services);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    public static function getServicesAndOffices(bool $showUnpublished = false): OfficeServiceAndRelationList|array
    {
        $cacheKey = self::CACHE_KEY_OFFICES_AND_SERVICES . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $requestList = ZmsApiClientService::getServices() ?? new RequestList();
        $relationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();

        $offices = MapperService::mapOfficesWithScope($providerList, $showUnpublished) ?? new OfficeList();
        $services = MapperService::mapServicesWithCombinations(
            $requestList,
            $relationList,
            $showUnpublished
        ) ?? new ServiceList();
        $relations = MapperService::mapRelations($relationList, $showUnpublished) ?? new OfficeServiceRelationList();

        $result = new OfficeServiceAndRelationList($offices, $services, $relations);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    /* Todo add method
     * getCombinableServicesByIds
     *
     *
     *
     */

    public static function getScopeByOfficeId(int $officeId): ThinnedScope|array
    {
        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $provider = null;
        foreach ($providerList as $p) {
            if ((int) $p->id === (int) $officeId) {
                $provider = $p;
                break;
            }
        }
        if (!$provider) {
            return ['errors' => [self::getError('officeNotFound')]];
        }

        $scopeSource = (string) ($provider->source ?? '');
        if ($scopeSource === '') {
            return ['errors' => [self::getError('scopeNotFound')]];
        }

        $matchingScope = ZmsApiClientService::getScopesByProviderId($scopeSource, (int) $officeId)
            ->getIterator()
            ->current();

        if (!$matchingScope instanceof Scope) {
            return ['errors' => [self::getError('scopeNotFound')]];
        }

        $providerMap = [];
        foreach ($providerList as $prov) {
            $key = ($prov->source ?? '') . '_' . $prov->id;
            $providerMap[$key] = $prov;
        }

        $scopeProvider = $matchingScope->getProvider();
        $providerKey = $scopeProvider ? (($scopeProvider->source ?? '') . '_' . $scopeProvider->id) : null;
        $finalProvider = ($providerKey && isset($providerMap[$providerKey]))
            ? $providerMap[$providerKey]
            : $scopeProvider;

        return new ThinnedScope(
            id: (int) $matchingScope->id,
            provider: MapperService::providerToThinnedProvider($finalProvider) ?? null,
            shortName: (string) $matchingScope->getShortName() ?? null,
            emailFrom: (string) $matchingScope->getEmailFrom() ?? null,
            emailRequired: (bool) $matchingScope->getEmailRequired() ?? null,
            telephoneActivated: (bool) $matchingScope->getTelephoneActivated() ?? null,
            telephoneRequired: (bool) $matchingScope->getTelephoneRequired() ?? null,
            customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated() ?? null,
            customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired() ?? null,
            customTextfieldLabel: $matchingScope->getCustomTextfieldLabel() ?? null,
            customTextfield2Activated: (bool) $matchingScope->getCustomTextfield2Activated() ?? null,
            customTextfield2Required: (bool) $matchingScope->getCustomTextfield2Required() ?? null,
            customTextfield2Label: $matchingScope->getCustomTextfield2Label() ?? null,
            captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired() ?? null,
            displayInfo: $matchingScope->getDisplayInfo() ?? null,
            slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()) ?? null,
            appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()) ?? null,
            whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails()) ?? null
        );
    }

    /* Todo add method
     * getOfficeById
     *
     *
     *
     */

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @TODO: Extract providerMap mapping logic into MapperService
     */
    public static function getOfficeListByServiceId(int $serviceId, bool $showUnpublished = false): OfficeList|array
    {
        $cacheKey = self::CACHE_KEY_OFFICES_BY_SERVICE_PREFIX . $serviceId . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
        $providerMap = [];
        foreach ($providerList as $provider) {
            if (!$showUnpublished && isset($provider->data['public']) && !(bool) $provider->data['public']) {
                continue;
            }

            $providerMap[$provider->id] = $provider;
        }

        $offices = [];
        foreach ($requestRelationList as $relation) {
            if ((int) $relation->request->id === $serviceId) {
                $providerId = $relation->provider->id;
                if (!isset($providerMap[$providerId])) {
                    continue;
                }

                $provider = $providerMap[$providerId];
                $scope = null;
                $scopeData = self::getScopeByOfficeId((int) $provider->id);
                if ($scopeData instanceof ThinnedScope) {
                    $scope = $scopeData;
                }

                $offices[] = new Office(
                    id: (int) $provider->id,
                    name: $provider->name,
                    showAlternativeLocations: $provider->data['showAlternativeLocations'] ?? null,
                    displayNameAlternatives: $provider->data['displayNameAlternatives'] ?? [],
                    organization: $provider->data['organization'] ?? null,
                    organizationUnit: $provider->data['organizationUnit'] ?? null,
                    slotTimeInMinutes: $provider->data['slotTimeInMinutes'] ?? null,
                    address: $provider->address ?? null,
                    geo: $provider->geo ?? null,
                    scope: $scope,
                    maxSlotsPerAppointment: $scope ? ((string) $scope->getSlotsPerAppointment() === '' ? null : (string) $scope->getSlotsPerAppointment()) : null
                );
            }
        }

        $errors = ValidationService::validateOfficesNotFound($offices);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        $result = new OfficeList($offices);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getScopeById(?int $scopeId): ThinnedScope|array
    {
        $scopeList = ZmsApiClientService::getScopes() ?? new ScopeList();
        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $matchingScope = null;
        foreach ($scopeList as $scope) {
            if ((int) $scope->id === (int) $scopeId) {
                $matchingScope = $scope;
                break;
            }
        }

        $tempScopeList = new ScopeList();
        if ($matchingScope !== null) {
            $tempScopeList->addEntity($matchingScope);
        }
        $errors = ValidationService::validateScopesNotFound($tempScopeList);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        $providerMap = [];
        foreach ($providerList as $provider) {
            $key = $provider->source . '_' . $provider->id;
            $providerMap[$key] = $provider;
        }

        $scopeProvider = $matchingScope->getProvider();
        $providerKey = $scopeProvider ? ($scopeProvider->source . '_' . $scopeProvider->id) : null;
        $matchingProv = ($providerKey && isset($providerMap[$providerKey]))
            ? $providerMap[$providerKey]
            : $scopeProvider;
        return new ThinnedScope(
            id: (int) $matchingScope->id,
            provider: MapperService::providerToThinnedProvider($matchingProv),
            shortName: (string) $matchingScope->getShortName() ?? null,
            emailFrom: (string) $matchingScope->getEmailFrom() ?? null,
            emailRequired: (bool) $matchingScope->getEmailRequired() ?? null,
            telephoneActivated: (bool) $matchingScope->getTelephoneActivated() ?? null,
            telephoneRequired: (bool) $matchingScope->getTelephoneRequired() ?? null,
            customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated() ?? null,
            customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired() ?? null,
            customTextfieldLabel: $matchingScope->getCustomTextfieldLabel() ?? null,
            customTextfield2Activated: (bool) $matchingScope->getCustomTextfield2Activated() ?? null,
            customTextfield2Required: (bool) $matchingScope->getCustomTextfield2Required() ?? null,
            customTextfield2Label: $matchingScope->getCustomTextfield2Label() ?? null,
            captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired() ?? null,
            displayInfo: $matchingScope->getDisplayInfo() ?? null,
            slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()) ?? null,
            appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()) ?? null,
            whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails()) ?? null
        );
    }

    public static function getServicesByOfficeId(int $officeId, bool $showUnpublished = false): ServiceList|array
    {
        $cacheKey = self::CACHE_KEY_SERVICES_BY_OFFICE_PREFIX . $officeId . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $requestList = ZmsApiClientService::getServices() ?? new RequestList();
        $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
        $requestMap = [];
        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            if (
                !$showUnpublished
                && isset($additionalData['public'])
                && !$additionalData['public']
            ) {
                continue;
            }

            $requestMap[$request->id] = $request;
        }

        $services = [];
        foreach ($requestRelationList as $relation) {
            if ((int) $relation->provider->id === $officeId) {
                $requestId = $relation->request->id;
                if (isset($requestMap[$requestId])) {
                    $request = $requestMap[$requestId];
                    $services[] = new Service(id: (int) $request->id, name: $request->name, maxQuantity: $request->getAdditionalData()['maxQuantity'] ?? 1);
                }
            }
        }

        $errors = ValidationService::validateServicesNotFound($services);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        $result = new ServiceList($services);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    public static function getBookableFreeDays(
        array $officeIds,
        array $serviceIds,
        array $serviceCounts,
        string $startDate,
        string $endDate,
        ?bool $groupByOffice = false
    ): AvailableDays|AvailableDaysByOffice|array {
        $firstDay = DateTimeFormatHelper::getInternalDateFromISO($startDate);
        $lastDay  = DateTimeFormatHelper::getInternalDateFromISO($endDate);

        $providerList = ZmsApiClientService::getOffices()  ?? new ProviderList();
        $requestList  = ZmsApiClientService::getServices() ?? new RequestList();

        $providerSource = [];
        foreach ($providerList as $p) {
            $providerSource[(string)$p->id] = (string)($p->source ?? '');
        }

        $requestSource = [];
        foreach ($requestList as $r) {
            $requestSource[(string)$r->id] = (string)($r->source ?? '');
        }

        $services = [];
        foreach ($serviceIds as $i => $serviceId) {
            $sid = (string)$serviceId;
            $src = $requestSource[$sid] ?? null;
            if (!$src) {
                return ['errors' => [['message' => 'Unknown service source for ID '.$sid]]];
            }
            $services[] = [
                'id'        => $serviceId,
                'source'    => $src,
                'slotCount' => (int)($serviceCounts[$i] ?? 1),
            ];
        }

        $providers = [];
        foreach ($officeIds as $officeId) {
            $oid = (string)$officeId;
            $src = $providerSource[$oid] ?? null;
            if (!$src) {
                return ['errors' => [['message' => 'Unknown provider source for ID '.$oid]]];
            }
            $providers[] = [
                'id'     => $officeId,
                'source' => $src,
            ];
        }

        $freeDays = ZmsApiClientService::getFreeDays(
            new ProviderList($providers),
            new RequestList($services),
            $firstDay,
            $lastDay
        ) ?? new Calendar();

        $daysCollection  = $freeDays->days;
        $formattedDays   = [];
        $scopeToProvider = [];

        foreach ($freeDays->scopes as $scope) {
            $scopeToProvider[$scope['id']] = $scope['provider']['id'];
        }

        foreach ($daysCollection as $day) {
            $formattedDays[] = [
                'time'        => sprintf('%04d-%02d-%02d', $day->year, $day->month, $day->day),
                'providerIDs' => isset($day->scopeIDs)
                    ? implode(',', array_map(fn($scopeId) => $scopeToProvider[$scopeId], explode(',', $day->scopeIDs)))
                    : ''
            ];
        }

        $errors = ValidationService::validateAppointmentDaysNotFound($formattedDays);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        return $groupByOffice
            ? new AvailableDaysByOffice($formattedDays)
            : new AvailableDays(array_column($formattedDays, 'time'));
    }

    public static function getFreeAppointments(int $officeId, array $serviceIds, array $serviceCounts, array $date): ProcessList|array
    {
        $providerList = ZmsApiClientService::getOffices()  ?? new ProviderList();
        $requestList  = ZmsApiClientService::getServices() ?? new RequestList();

        $providerSource = [];
        foreach ($providerList as $p) { $providerSource[(string)$p->id] = (string)($p->source ?? ''); }
        $requestSource  = [];
        foreach ($requestList as $r) { $requestSource[(string)$r->id] = (string)($r->source ?? ''); }

        $oid = (string)$officeId;
        $provSrc = $providerSource[$oid] ?? null;
        if (!$provSrc) return ['errors' => [['message' => 'Unknown provider source for ID '.$oid]]];

        $office = ['id' => $officeId, 'source' => $provSrc];

        $requests = [];
        foreach ($serviceIds as $i => $serviceId) {
            $sid   = (string)$serviceId;
            $reqSrc= $requestSource[$sid] ?? null;
            if (!$reqSrc) return ['errors' => [['message' => 'Unknown service source for ID '.$sid]]];
            $count = (int)($serviceCounts[$i] ?? 1);
            for ($k = 0; $k < $count; $k++) {
                $requests[] = ['id' => $serviceId, 'source' => $reqSrc];
            }
        }

        return ZmsApiClientService::getFreeTimeslots(
            new ProviderList([$office]),
            new RequestList($requests),
            $date,
            $date
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private static function processFreeSlots(ProcessList $freeSlots, bool $groupByOffice = false): array
    {
        $errors = ValidationService::validateGetProcessFreeSlots($freeSlots);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        $currentTimestamp = time();
        if ($groupByOffice) {
            $grouped = [];
            foreach ($freeSlots as $slot) {
                $officeId = (string)($slot->scope->provider->id ?? '');
                if (!isset($grouped[$officeId])) {
                    $grouped[$officeId] = [];
                }
                if (isset($slot->appointments) && is_iterable($slot->appointments)) {
                    foreach ($slot->appointments as $appointment) {
                        if (isset($appointment->date)) {
                            $timestamp = (int) $appointment->date;
                            if ($timestamp > $currentTimestamp) {
                                $grouped[$officeId][] = $timestamp;
                            }
                        }
                    }
                }
            }
            // Sort each office's appointments
            foreach ($grouped as &$arr) {
                sort($arr);
            }
            unset($arr);
            // Optionally validate grouped timestamps here if needed
            return $grouped;
        } else {
            $timestamps = [];
            foreach ($freeSlots as $slot) {
                if (isset($slot->appointments) && is_iterable($slot->appointments)) {
                    foreach ($slot->appointments as $appointment) {
                        if (isset($appointment->date)) {
                            $timestamp = (int) $appointment->date;
                            if ($timestamp > $currentTimestamp) {
                                $timestamps[] = $timestamp;
                            }
                        }
                    }
                }
            }
            sort($timestamps);

            $errors = ValidationService::validateGetProcessByIdTimestamps($timestamps);
            if (is_array($errors) && !empty($errors['errors'])) {
                return $errors;
            }

            return $timestamps;
        }
    }

    public static function getAvailableAppointments(
        string $date,
        array $officeIds,
        array $serviceIds,
        array $serviceCounts,
        ?bool $groupByOffice = false
    ): AvailableAppointments|AvailableAppointmentsByOffice|array {
        $providerList = ZmsApiClientService::getOffices()  ?? new ProviderList();
        $requestList  = ZmsApiClientService::getServices() ?? new RequestList();

        $providerSource = [];
        foreach ($providerList as $p) { $providerSource[(string)$p->id] = (string)($p->source ?? ''); }
        $requestSource  = [];
        foreach ($requestList as $r) { $requestSource[(string)$r->id] = (string)($r->source ?? ''); }

        $requests = [];
        foreach ($serviceIds as $i => $serviceId) {
            $sid = (string)$serviceId;
            $src = $requestSource[$sid] ?? null;
            if (!$src) return ['errors' => [['message' => 'Unknown service source for ID '.$sid]]];
            $count = (int)($serviceCounts[$i] ?? 1);
            for ($k = 0; $k < $count; $k++) {
                $requests[] = ['id' => $serviceId, 'source' => $src];
            }
        }

        $providers = [];
        foreach ($officeIds as $officeId) {
            $oid = (string)$officeId;
            $src = $providerSource[$oid] ?? null;
            if (!$src) return ['errors' => [['message' => 'Unknown provider source for ID '.$oid]]];
            $providers[] = ['id' => $officeId, 'source' => $src];
        }

        $freeSlots = ZmsApiClientService::getFreeTimeslots(
            new ProviderList($providers),
            new RequestList($requests),
            DateTimeFormatHelper::getInternalDateFromISO($date),
            DateTimeFormatHelper::getInternalDateFromISO($date)
        ) ?? new ProcessList();

        $result = self::processFreeSlots($freeSlots, $groupByOffice);
        if (isset($result['errors']) && !empty($result['errors'])) return $result;

        return $groupByOffice
            ? new AvailableAppointmentsByOffice($result)
            : new AvailableAppointments($result);
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): ThinnedProcess|array
    {
        $errors = ValidationService::validateServiceArrays($serviceIds, $serviceCounts);
        if (!empty($errors)) {
            return $errors;
        }
        $process = ZmsApiClientService::reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts);
        return MapperService::processToThinnedProcess($process);
    }

    public static function getThinnedProcessById(?int $processId, ?string $authKey): ThinnedProcess|array
    {

        $process = ZmsApiClientService::getProcessById($processId, $authKey);
        $errors = ValidationService::validateGetProcessNotFound($process);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }
        $thinnedProcess = MapperService::processToThinnedProcess($process);

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $providerMap = [];
        foreach ($providerList as $provider) {
            $key = $provider->getSource() . '_' . $provider->id;
            $providerMap[$key] = $provider;
        }

        $thinnedScope = null;
        if ($process->scope instanceof Scope) {
            $scopeProvider = $process->scope->getProvider();
            $providerKey = $scopeProvider ? ($scopeProvider->getSource() . '_' . $scopeProvider->id) : null;
            $matchingProvider = $providerKey && isset($providerMap[$providerKey]) ? $providerMap[$providerKey] : $scopeProvider;
            $thinnedProvider = MapperService::providerToThinnedProvider($matchingProvider);
            $thinnedScope = new ThinnedScope(
                id: (int) $process->scope->id,
                provider: $thinnedProvider,
                shortName: (string) $process->scope->getShortName() ?? null,
                emailFrom: (string) $process->scope->getEmailFrom() ?? null,
                emailRequired: (bool) $process->scope->getEmailRequired() ?? false,
                telephoneActivated: (bool) $process->scope->getTelephoneActivated() ?? false,
                telephoneRequired: (bool) $process->scope->getTelephoneRequired() ?? false,
                customTextfieldActivated: (bool) $process->scope->getCustomTextfieldActivated() ?? false,
                customTextfieldRequired: (bool) $process->scope->getCustomTextfieldRequired() ?? false,
                customTextfieldLabel: $process->scope->getCustomTextfieldLabel() ?? null,
                customTextfield2Activated: (bool) $process->scope->getCustomTextfield2Activated() ?? false,
                customTextfield2Required: (bool) $process->scope->getCustomTextfield2Required() ?? false,
                customTextfield2Label: $process->scope->getCustomTextfield2Label() ?? null,
                captchaActivatedRequired: (bool) $process->scope->getCaptchaActivatedRequired() ?? false,
                displayInfo: $process->scope->getDisplayInfo() ?? null,
                slotsPerAppointment: ((string) $process->scope->getSlotsPerAppointment() === '' ? null : (string) $process->scope->getSlotsPerAppointment()) ?? null,
                appointmentsPerMail: ((string) $process->scope->getAppointmentsPerMail() === '' ? null : (string) $process->scope->getAppointmentsPerMail()) ?? null,
                whitelistedMails: ((string) $process->scope->getWhitelistedMails() === '' ? null : (string) $process->scope->getWhitelistedMails()) ?? null
            );
        }

        $thinnedProcess->scope = $thinnedScope;
        return $thinnedProcess;
    }

    public static function updateClientData(Process $reservedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::submitClientData($reservedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }

    public static function preconfirmAppointment(Process $reservedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::preconfirmProcess($reservedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }

    public static function confirmAppointment(Process $preconfirmedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::confirmProcess($preconfirmedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }

    public static function cancelAppointment(Process $confirmedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::cancelAppointment($confirmedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }

    public static function sendPreconfirmationEmail(Process $reservedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::sendPreconfirmationEmail($reservedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }

    public static function sendConfirmationEmail(Process $preconfirmedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::sendConfirmationEmail($preconfirmedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }

    public static function sendCancellationEmail(Process $confirmedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::sendCancellationEmail($confirmedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }
}
