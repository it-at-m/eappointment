<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Slim\LoggerService;
use BO\Zmscitizenapi\Exceptions\UnauthorizedException;
use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Utils\DateTimeFormatHelper;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Models\AvailableCalendarByOffice;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
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
    private const CACHE_KEY_SERVICES_BY_OFFICE_PREFIX = 'processed_services_by_office_';

    private static function getError(string $key): array
    {
        return ErrorMessages::get($key);
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

        $providerList = ZmsApiClientService::getOffices();
        $scopeList = ZmsApiClientService::getScopes();
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
                    infoForAppointment: $matchingScope->getInfoForAppointment(),
                    infoForAllAppointments: $matchingScope->getInfoForAllAppointments(),
                    slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()),
                    appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()),
                    whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails()),
                    activationDuration: MapperService::extractActivationDuration($matchingScope),
                    reservationDuration: (int) MapperService::extractReservationDuration($matchingScope),
                    hint: ($matchingScope && trim((string) $matchingScope->getScopeHint()) !== '')  ? (string) $matchingScope->getScopeHint() : null
                ) : null,
                slotsPerAppointment: $matchingScope ? ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()) : null
            );
        }

        $result = new OfficeList($offices);

        self::setMappedCache($cacheKey, $result);

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getScopes(): ThinnedScopeList|array
    {
        $cacheKey = self::CACHE_KEY_SCOPES;

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $providerList = ZmsApiClientService::getOffices();
        $scopeList = ZmsApiClientService::getScopes();
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
                    infoForAppointment: $matchingScope->getInfoForAppointment(),
                    infoForAllAppointments: $matchingScope->getInfoForAllAppointments(),
                    slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()),
                    appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()),
                    whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails()),
                    reservationDuration: (int) MapperService::extractReservationDuration($matchingScope),
                    activationDuration: MapperService::extractActivationDuration($matchingScope),
                    hint: ($matchingScope && trim((string) $matchingScope->getScopeHint()) !== '') ? (string) $matchingScope->getScopeHint() : null
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

        $requestList = ZmsApiClientService::getServices();
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

        $providerList = ZmsApiClientService::getOffices();
        $requestList = ZmsApiClientService::getServices();
        $relationList = ZmsApiClientService::getRequestRelationList();

        $offices = MapperService::mapOfficesWithScope($providerList, $showUnpublished);
        $services = MapperService::mapServicesWithCombinations(
            $requestList,
            $relationList,
            $showUnpublished
        );
        $relations = MapperService::mapRelations($relationList, $showUnpublished);

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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getScopeByOfficeId(int $officeId): ThinnedScope|array
    {
        $providerList = ZmsApiClientService::getOffices();
        $selectedProvider = null;
        foreach ($providerList as $provider) {
            if ((int) $provider->id === (int) $officeId) {
                $selectedProvider = $provider;
                break;
            }
        }
        if (!$selectedProvider) {
            return ['errors' => [self::getError('officeNotFound')]];
        }

        $scopeSource = (string) ($selectedProvider->source ?? '');
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
        $result = [
            'id' => $matchingScope->id,
            'provider' => MapperService::providerToThinnedProvider($finalProvider),
            'shortName' => (string) $matchingScope->getShortName(),
            'emailFrom' => (string) $matchingScope->getEmailFrom(),
            'emailRequired' => (bool) $matchingScope->getEmailRequired(),
            'telephoneActivated' => (bool) $matchingScope->getTelephoneActivated(),
            'telephoneRequired' => (bool) $matchingScope->getTelephoneRequired(),
            'customTextfieldActivated' => (bool) $matchingScope->getCustomTextfieldActivated(),
            'customTextfieldRequired' => (bool) $matchingScope->getCustomTextfieldRequired(),
            'customTextfieldLabel' => $matchingScope->getCustomTextfieldLabel() ?? null,
            'customTextfield2Activated' => (bool) $matchingScope->getCustomTextfield2Activated(),
            'customTextfield2Required' => (bool) $matchingScope->getCustomTextfield2Required(),
            'customTextfield2Label' => $matchingScope->getCustomTextfield2Label() ?? null,
            'captchaActivatedRequired' => (bool) $matchingScope->getCaptchaActivatedRequired(),
            'infoForAppointment' => $matchingScope->getInfoForAppointment() ?? null,
            'infoForAllAppointments' => $matchingScope->getInfoForAllAppointments() ?? null,
            'slotsPerAppointment' => ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()),
            'appointmentsPerMail' => ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()),
            'whitelistedMails' => ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails()),
            'reservationDuration' => (int) MapperService::extractReservationDuration($matchingScope),
            'activationDuration' => MapperService::extractActivationDuration($matchingScope),
            'hint' => (trim((string) ($matchingScope->getScopeHint() ?? '')) === '') ? null : (string) $matchingScope->getScopeHint()
        ];
        return new ThinnedScope(
            id: (int) $result['id'],
            provider: $result['provider'],
            shortName: $result['shortName'],
            emailFrom: $result['emailFrom'],
            emailRequired: $result['emailRequired'],
            telephoneActivated: $result['telephoneActivated'],
            telephoneRequired: $result['telephoneRequired'],
            customTextfieldActivated: $result['customTextfieldActivated'],
            customTextfieldRequired: $result['customTextfieldRequired'],
            customTextfieldLabel: $result['customTextfieldLabel'],
            customTextfield2Activated: $result['customTextfield2Activated'],
            customTextfield2Required: $result['customTextfield2Required'],
            customTextfield2Label: $result['customTextfield2Label'],
            captchaActivatedRequired: $result['captchaActivatedRequired'],
            infoForAppointment: $result['infoForAppointment'],
            infoForAllAppointments: $result['infoForAllAppointments'],
            slotsPerAppointment: $result['slotsPerAppointment'],
            appointmentsPerMail: $result['appointmentsPerMail'],
            whitelistedMails: $result['whitelistedMails'],
            reservationDuration: $result['reservationDuration'],
            activationDuration: $result['activationDuration'],
            hint: $result['hint']
        );
    }

    /**
     * One offices + scopes load, then in-memory captcha checks for all office IDs.
     * Avoids N× getScopeByOfficeId (each re-loads source data and builds a full ThinnedScope).
     */
    public static function isCaptchaRequiredForAnyOffice(array $officeIds): bool
    {
        $wanted = [];
        foreach ($officeIds as $officeIdRaw) {
            $officeId = (int) $officeIdRaw;
            if ($officeId > 0) {
                $wanted[$officeId] = true;
            }
        }
        if ($wanted === []) {
            return false;
        }

        $providerById = [];
        foreach (ZmsApiClientService::getOffices() as $provider) {
            $providerById[(int) $provider->id] = $provider;
        }

        $scopes = ZmsApiClientService::getScopes();
        if (!$scopes instanceof ScopeList) {
            return false;
        }

        foreach (array_keys($wanted) as $officeId) {
            $provider = $providerById[$officeId] ?? null;
            if ($provider === null) {
                continue;
            }
            $source = (string) ($provider->source ?? '');
            if ($source === '') {
                continue;
            }

            $matchingScope = $scopes->withProviderID($source, (string) $officeId)->getIterator()->current();
            if ($matchingScope instanceof Scope && $matchingScope->getCaptchaActivatedRequired()) {
                return true;
            }
        }

        return false;
    }

    /* Todo add method
     * getOfficeById
     *
     *
     *
     */

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getScopeById(?int $scopeId): ThinnedScope|array
    {
        $scopeList = ZmsApiClientService::getScopes();
        $providerList = ZmsApiClientService::getOffices();
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

        if ($matchingScope === null) {
            return ValidationService::validateScopesNotFound(new ScopeList());
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
            shortName: (string) $matchingScope->getShortName(),
            emailFrom: (string) $matchingScope->getEmailFrom(),
            emailRequired: (bool) $matchingScope->getEmailRequired(),
            telephoneActivated: (bool) $matchingScope->getTelephoneActivated(),
            telephoneRequired: (bool) $matchingScope->getTelephoneRequired(),
            customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(),
            customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(),
            customTextfieldLabel: $matchingScope->getCustomTextfieldLabel() ?? null,
            customTextfield2Activated: (bool) $matchingScope->getCustomTextfield2Activated(),
            customTextfield2Required: (bool) $matchingScope->getCustomTextfield2Required(),
            customTextfield2Label: $matchingScope->getCustomTextfield2Label() ?? null,
            captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(),
            infoForAppointment: $matchingScope->getInfoForAppointment() ?? null,
            infoForAllAppointments: $matchingScope->getInfoForAllAppointments() ?? null,
            slotsPerAppointment: ((string) $matchingScope->getSlotsPerAppointment() === '' ? null : (string) $matchingScope->getSlotsPerAppointment()),
            appointmentsPerMail: ((string) $matchingScope->getAppointmentsPerMail() === '' ? null : (string) $matchingScope->getAppointmentsPerMail()),
            whitelistedMails: ((string) $matchingScope->getWhitelistedMails() === '' ? null : (string) $matchingScope->getWhitelistedMails()),
            reservationDuration: (int) MapperService::extractReservationDuration($matchingScope),
            activationDuration: MapperService::extractActivationDuration($matchingScope),
            hint: ((string) $matchingScope->getScopeHint() === '' ? null : (string) $matchingScope->getScopeHint())
        );
    }

    public static function getServicesByOfficeId(int $officeId, bool $showUnpublished = false): ServiceList|array
    {
        $t0 = microtime(true);
        $cacheKey = self::CACHE_KEY_SERVICES_BY_OFFICE_PREFIX . $officeId . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            LoggerService::logInfo('calendar.availability.timing', [
                'stage' => 'facade.getServicesByOfficeId',
                'office_id' => $officeId,
                'cache' => 'hit',
                'ms' => (int) round((microtime(true) - $t0) * 1000),
            ]);
            return $cachedData;
        }

        $t1 = microtime(true);
        $requestList = ZmsApiClientService::getServices();
        $t2 = microtime(true);
        $requestRelationList = ZmsApiClientService::getRequestRelationList();
        $t3 = microtime(true);
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

        LoggerService::logInfo('calendar.availability.timing', [
            'stage' => 'facade.getServicesByOfficeId',
            'office_id' => $officeId,
            'cache' => 'miss',
            'get_services_ms' => (int) round(($t2 - $t1) * 1000),
            'get_relations_ms' => (int) round(($t3 - $t2) * 1000),
            'map_ms' => (int) round((microtime(true) - $t3) * 1000),
            'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            'service_count' => count($services),
        ]);

        return $result;
    }

    public static function getCalendarAvailability(
        array $officeIds,
        array $serviceIds,
        array $serviceCounts,
        string $startDate,
        string $endDate,
        ?string $traceId = null
    ): AvailableCalendarByOffice|array {
        $t0 = microtime(true);
        $params = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'officeId' => implode(',', $officeIds),
            'serviceId' => implode(',', $serviceIds),
        ];
        if ($serviceCounts !== []) {
            $params['serviceCount'] = implode(',', $serviceCounts);
        }
        if ($traceId !== null && $traceId !== '') {
            $params['traceId'] = $traceId;
        }

        $availability = ZmsApiClientService::getCalendarAvailability($params, $traceId);
        $tAfterHttp = microtime(true);

        $formattedDays = [];
        foreach ($availability['days'] ?? [] as $day) {
            $offices = [];
            foreach ($day['appointments'] ?? [] as $officeId => $timestamps) {
                $offices[] = [
                    'officeId' => (string) $officeId,
                    'appointments' => array_map('intval', array_values((array) $timestamps)),
                ];
            }

            $formattedDays[] = [
                'time' => (string) ($day['date'] ?? ''),
                'providerIDs' => (string) ($day['providerIDs'] ?? ''),
                'offices' => $offices,
            ];
        }
        $tAfterFormat = microtime(true);

        $entity = new AvailableCalendarByOffice(
            (string) ($availability['startDate'] ?? $startDate),
            (string) ($availability['endDate'] ?? $endDate),
            $formattedDays
        );

        LoggerService::logInfo('calendar.availability.timing', [
            'trace_id' => $traceId,
            'stage' => 'facade.getCalendarAvailability',
            'http_ms' => (int) round(($tAfterHttp - $t0) * 1000),
            'format_ms' => (int) round(($tAfterFormat - $tAfterHttp) * 1000),
            'dto_ms' => (int) round((microtime(true) - $tAfterFormat) * 1000),
            'total_ms' => (int) round((microtime(true) - $t0) * 1000),
            'day_count' => count($formattedDays),
        ]);

        return $entity;
    }

    public static function getFreeAppointments(int $officeId, array $serviceIds, array $serviceCounts, array $date): ProcessList|array
    {
        $providerList = ZmsApiClientService::getOffices();
        $requestList  = ZmsApiClientService::getServices();

        $providerSource = [];
        foreach ($providerList as $p) {
            $providerSource[(string)$p->id] = (string)($p->source ?? '');
        }
        $requestSource  = [];
        foreach ($requestList as $r) {
            $requestSource[(string)$r->id] = (string)($r->source ?? '');
        }

        $oid = (string)$officeId;
        $provSrc = $providerSource[$oid] ?? null;
        if (!$provSrc) {
            return ['errors' => [['message' => 'Unknown provider source for ID ' . $oid]]];
        }

        $office = ['id' => $officeId, 'source' => $provSrc];

        $requests = [];
        foreach ($serviceIds as $id => $serviceId) {
            $sid   = (string)$serviceId;
            $reqSrc = $requestSource[$sid] ?? null;
            if (!$reqSrc) {
                return ['errors' => [['message' => 'Unknown service source for ID ' . $sid]]];
            }
            $count = (int)($serviceCounts[$id] ?? 1);
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

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): ThinnedProcess|array
    {
        $errors = ValidationService::validateServiceArrays($serviceIds, $serviceCounts);
        if (!empty($errors)) {
            return $errors;
        }
        $process = ZmsApiClientService::reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts);
        return MapperService::processToThinnedProcess($process);
    }

    public static function getProcessById(?int $processId, ?string $authKey, ?AuthenticatedUser $user): Process
    {
        // AuthKey check needs to be first
        if (!is_null($authKey)) {
            return ZmsApiClientService::getProcessById($processId, $authKey);
        } elseif (!is_null($user)) {
            $externalUserId = $user->getExternalUserId();
            return ZmsApiClientService::getProcessByIdAuthenticated($processId, $externalUserId);
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getThinnedProcessById(int $processId, ?string $authKey, ?AuthenticatedUser $user): ThinnedProcess|array
    {
        $process = self::getProcessById($processId, $authKey, $user);
        $errors = ValidationService::validateGetProcessNotFound($process);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }
        $thinnedProcess = MapperService::processToThinnedProcess($process);

        $providerList = ZmsApiClientService::getOffices();
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
                shortName: (string) $process->scope->getShortName(),
                emailFrom: (string) $process->scope->getEmailFrom(),
                emailRequired: (bool) $process->scope->getEmailRequired(),
                telephoneActivated: (bool) $process->scope->getTelephoneActivated(),
                telephoneRequired: (bool) $process->scope->getTelephoneRequired(),
                customTextfieldActivated: (bool) $process->scope->getCustomTextfieldActivated(),
                customTextfieldRequired: (bool) $process->scope->getCustomTextfieldRequired(),
                customTextfieldLabel: $process->scope->getCustomTextfieldLabel() ?? null,
                customTextfield2Activated: (bool) $process->scope->getCustomTextfield2Activated(),
                customTextfield2Required: (bool) $process->scope->getCustomTextfield2Required(),
                customTextfield2Label: $process->scope->getCustomTextfield2Label() ?? null,
                captchaActivatedRequired: (bool) $process->scope->getCaptchaActivatedRequired(),
                infoForAppointment: $process->scope->getInfoForAppointment() ?? null,
                infoForAllAppointments: $process->scope->getInfoForAllAppointments() ?? null,
                slotsPerAppointment: ((string) $process->scope->getSlotsPerAppointment() === '' ? null : (string) $process->scope->getSlotsPerAppointment()),
                appointmentsPerMail: ((string) $process->scope->getAppointmentsPerMail() === '' ? null : (string) $process->scope->getAppointmentsPerMail()),
                whitelistedMails: ((string) $process->scope->getWhitelistedMails() === '' ? null : (string) $process->scope->getWhitelistedMails()),
                reservationDuration: (int) MapperService::extractReservationDuration($process->scope),
                activationDuration: MapperService::extractActivationDuration($process->scope),
                hint: ((string) $process->scope->getScopeHint() === '' ? null : (string) $process->scope->getScopeHint())
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

    public static function getAppointmentsByExternalUserId(string $externalUserId, ?int $filterId = null, ?string $status = null): ProcessList
    {
        return ZmsApiClientService::getProcessesByExternalUserId($externalUserId, $filterId, $status);
    }
}
