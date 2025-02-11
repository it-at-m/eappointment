<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\AvailableAppointmentsByOffice;
use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\ProcessFreeSlots;
use BO\Zmscitizenapi\Models\ProcessFreeSlotsGroupByOffice;
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
    private static ?string $currentLanguage = null;
    public static function setLanguageContext(?string $language): void
    {
        self::$currentLanguage = $language;
    }

    private static function getError(string $key): array
    {
        return ErrorMessages::get($key, self::$currentLanguage);
    }

    public static function getOffices(bool $showUnpublished = false): OfficeList
    {
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
            if (! $showUnpublished && isset($provider->data['public']) && ! (bool) $provider->data['public']) {
                continue;
            }

            $matchingScope = $scopeMap[$provider->source . '_' . $provider->id] ?? null;
            $offices[] = new Office(id: (int) $provider->id, name: $provider->displayName ?? $provider->name, address: $provider->data['address'] ?? null, showAlternativeLocations: $provider->data['showAlternativeLocations'] ?? null, displayNameAlternatives: $provider->data['displayNameAlternatives'] ?? [], organization: $provider->data['organization'] ?? null, organizationUnit: $provider->data['organizationUnit'] ?? null, slotTimeInMinutes: $provider->data['slotTimeInMinutes'] ?? null, geo: $provider->data['geo'] ?? null, scope: $matchingScope ? new ThinnedScope(id: (int) $matchingScope->id, provider: MapperService::providerToThinnedProvider($provider), shortName: $matchingScope->getShortName(), telephoneActivated: (bool) $matchingScope->getTelephoneActivated(), telephoneRequired: (bool) $matchingScope->getTelephoneRequired(), customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(), customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(), customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(), captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(), displayInfo: $matchingScope->getDisplayInfo()) : null);
        }

        return new OfficeList($offices);
    }

    public static function getScopes(): ThinnedScopeList|array
    {
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
                $scopesProjectionList[] = new ThinnedScope(id: (int) $matchingScope->id, provider: MapperService::providerToThinnedProvider($provider), shortName: $matchingScope->getShortName(), telephoneActivated: (bool) $matchingScope->getTelephoneActivated(), telephoneRequired: (bool) $matchingScope->getTelephoneRequired(), customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(), customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(), customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(), captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(), displayInfo: $matchingScope->getDisplayInfo());
            }
        }

        return new ThinnedScopeList($scopesProjectionList);
    }

    public static function getServices(bool $showUnpublished = false): ServiceList|array
    {
        $requestList = ZmsApiClientService::getServices($showUnpublished) ?? new RequestList();
        $services = [];
        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            if (
                ! $showUnpublished
                && isset($additionalData['public'])
                && !$additionalData['public']
            ) {
                continue;
            }

            $services[] = new Service(id: (int) $request->getId(), name: $request->getName(), maxQuantity: $additionalData['maxQuantity'] ?? 1);
        }

        return new ServiceList($services);
    }

    public static function getServicesAndOffices(bool $showUnpublished = false): OfficeServiceAndRelationList|array
    {
        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $requestList = ZmsApiClientService::getServices() ?? new RequestList();
        $relationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();

        $offices = MapperService::mapOfficesWithScope($providerList, $showUnpublished) ?? new OfficeList();
        $services = MapperService::mapServicesWithCombinations(
            $requestList,
            $relationList,
            $showUnpublished
        ) ?? new ServiceList();
        $relations = MapperService::mapRelations($relationList) ?? new OfficeServiceRelationList();
        return new OfficeServiceAndRelationList($offices, $services, $relations);
    }

    /* Todo add method
     * getCombinableServicesByIds
     *
     *
     *
     */

    public static function getScopeByOfficeId(int $officeId): ThinnedScope|array
    {
        $matchingScope = ZmsApiClientService::getScopesByProviderId(\App::$source_name, $officeId)->getIterator()->current();
        if (!$matchingScope instanceof Scope) {
            return ['errors' => [self::getError('scopeNotFound')]];
        }

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $providerMap = [];
        foreach ($providerList as $provider) {
            $key = $provider->source . '_' . $provider->id;
            $providerMap[$key] = $provider;
        }

        $scopeProvider = $matchingScope->getProvider();
        $providerKey = $scopeProvider ? ($scopeProvider->source . '_' . $scopeProvider->id) : null;
        $finalProvider = $providerKey && isset($providerMap[$providerKey])
            ? $providerMap[$providerKey]
            : $scopeProvider;
        $result = [
            'id' => $matchingScope->id,
            'provider' => MapperService::providerToThinnedProvider($finalProvider) ?? null,
            'shortName' => $matchingScope->getShortName() ?? null,
            'telephoneActivated' => (bool) $matchingScope->getTelephoneActivated() ?? null,
            'telephoneRequired' => (bool) $matchingScope->getTelephoneRequired() ?? null,
            'customTextfieldActivated' => (bool) $matchingScope->getCustomTextfieldActivated() ?? null,
            'customTextfieldRequired' => (bool) $matchingScope->getCustomTextfieldRequired() ?? null,
            'customTextfieldLabel' => $matchingScope->getCustomTextfieldLabel() ?? null,
            'captchaActivatedRequired' => (bool) $matchingScope->getCaptchaActivatedRequired() ?? null,
            'displayInfo' => $matchingScope->getDisplayInfo() ?? null,
        ];
        return new ThinnedScope(id: (int) $result['id'], provider: $result['provider'], shortName: $result['shortName'], telephoneActivated: $result['telephoneActivated'], telephoneRequired: $result['telephoneRequired'], customTextfieldActivated: $result['customTextfieldActivated'], customTextfieldRequired: $result['customTextfieldRequired'], customTextfieldLabel: $result['customTextfieldLabel'], captchaActivatedRequired: $result['captchaActivatedRequired'], displayInfo: $result['displayInfo']);
    }

    /* Todo add method
     * getOfficeById
     *
     *
     *
     */

    public static function getOfficeListByServiceId(int $serviceId, bool $showUnpublished = false): OfficeList|array
    {
        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
        $providerMap = [];
        foreach ($providerList as $provider) {
            if (! $showUnpublished && isset($provider->data['public']) && ! (bool) $provider->data['public']) {
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

                $offices[] = new Office(id: (int) $provider->id, name: $provider->name, showAlternativeLocations: $provider->data['showAlternativeLocations'] ?? null, displayNameAlternatives: $provider->data['displayNameAlternatives'] ?? [], organization: $provider->data['organization'] ?? null, organizationUnit: $provider->data['organizationUnit'] ?? null, slotTimeInMinutes: $provider->data['slotTimeInMinutes'] ?? null, address: $provider->address ?? null, geo: $provider->geo ?? null, scope: $scope);
            }
        }

        $errors = ValidationService::validateOfficesNotFound($offices);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        return new OfficeList($offices);
    }

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
        return new ThinnedScope(id: (int) $matchingScope->id, provider: MapperService::providerToThinnedProvider($matchingProv), shortName: $matchingScope->getShortName() ?? null, telephoneActivated: (bool) $matchingScope->getTelephoneActivated() ?? null, telephoneRequired: (bool) $matchingScope->getTelephoneRequired() ?? null, customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated() ?? null, customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired() ?? null, customTextfieldLabel: $matchingScope->getCustomTextfieldLabel() ?? null, captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired() ?? null, displayInfo: $matchingScope->getDisplayInfo() ?? null);
    }

    public static function getServicesByOfficeId(int $officeId, bool $showUnpublished = false): ServiceList|array
    {
        $requestList = ZmsApiClientService::getServices() ?? new RequestList();
        $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
        $requestMap = [];
        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            if (
                ! $showUnpublished
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

        return new ServiceList($services);
    }

    public static function getOfficesThatProvideService(int $serviceId): OfficeList|array
    {
        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
        $providerIds = [];
        foreach ($requestRelationList as $relation) {
            if ((int) $relation->request->id === $serviceId) {
                $providerIds[] = $relation->provider->id;
            }
        }

        $offices = [];
        foreach ($providerList as $provider) {
            if (
                in_array($provider->id, $providerIds) &&
                isset($provider->data['public']) &&
                $provider->data['public'] === true
            ) {
                $scope = self::getScopeByOfficeId((int) $provider->id);
                if (!is_array($scope)) {
                    $offices[] = new Office(id: (int) $provider->id, name: $provider->displayName ?? $provider->name, address: $provider->data['address'] ?? null, showAlternativeLocations: $provider->data['showAlternativeLocations'] ?? null, displayNameAlternatives: $provider->data['displayNameAlternatives'] ?? [], organization: $provider->data['organization'] ?? null, organizationUnit: $provider->data['organizationUnit'] ?? null, slotTimeInMinutes: $provider->data['slotTimeInMinutes'] ?? null, geo: $provider->data['geo'] ?? null, scope: $scope instanceof ThinnedScope ? $scope : null);
                }
            }
        }

        $errors = ValidationService::validateOfficesNotFound($offices);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        return new OfficeList($offices);
    }

    public static function getServicesProvidedAtOffice(int $officeId): RequestList|array
    {
        $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
        $requestRelationArray = [];
        foreach ($requestRelationList as $relation) {
            $requestRelationArray[] = $relation;
        }

        $serviceIds = array_filter($requestRelationArray, function ($relation) use ($officeId) {

            return $relation->provider->id === $officeId || (string) $relation->provider->id === (string) $officeId;
        });
        $serviceIds = array_map(function ($relation) {

            return $relation->request->id;
        }, $serviceIds);
        $requestList = ZmsApiClientService::getServices() ?? new RequestList();
        $requestArray = [];
        foreach ($requestList as $request) {
            $requestArray[] = $request;
        }

        $filteredRequests = array_filter($requestArray, function ($request) use ($serviceIds) {

            return in_array($request->id, $serviceIds);
        });
        $resultRequestList = new RequestList();
        foreach ($filteredRequests as $request) {
            $resultRequestList->addEntity($request);
        }

        return $resultRequestList;
    }

    public static function getBookableFreeDays(array $officeIds, array $serviceIds, array $serviceCounts, string $startDate, string $endDate): AvailableDays|array
    {
        $firstDay = DateTimeFormatHelper::getInternalDateFromISO($startDate);
        $lastDay = DateTimeFormatHelper::getInternalDateFromISO($endDate);
        $services = [];
        $providers = [];
        $serviceNumber = 0;
        foreach ($serviceIds as $serviceId) {
            $services[] = [
                'id' => $serviceId,
                'source' => \App::$source_name,
                'slotCount' => $serviceCounts[$serviceNumber],
            ];
            $serviceNumber++;
        }

        foreach ($officeIds as $officeId) {
            $providers[] = [
                'id' => $officeId,
                'source' => \App::$source_name,
            ];
        }

        $freeDays = ZmsApiClientService::getFreeDays(new ProviderList($providers), new RequestList($services), $firstDay, $lastDay,) ?? new Calendar();
        $daysCollection = $freeDays->days;
        $formattedDays = [];
        foreach ($daysCollection as $day) {
            $formattedDays[] = sprintf('%04d-%02d-%02d', $day->year, $day->month, $day->day);
        }

        $errors = ValidationService::validateAppointmentDaysNotFound($formattedDays);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        return new AvailableDays($formattedDays);
    }

    public static function getFreeAppointments(int $officeId, array $serviceIds, array $serviceCounts, array $date): ProcessList|array
    {
        $office = [
            'id' => $officeId,
            'source' => \App::$source_name
        ];
        $requests = [];
        foreach ($serviceIds as $index => $serviceId) {
            $service = [
                'id' => $serviceId,
                'source' => \App::$source_name,
                'slotCount' => $serviceCounts[$index]
            ];
            $requests = array_merge($requests, array_fill(0, $service['slotCount'], $service));
        }

        return ZmsApiClientService::getFreeTimeslots(new ProviderList([$office]), new RequestList($requests), $date, $date);
    }

    public static function getAvailableAppointments(string $date, array $officeIds, array $serviceIds, array $serviceCounts, ?bool $groupByOffice = false): AvailableAppointments|AvailableAppointmentsByOffice|array
    {
        $requests = [];
        $providers = [];
        foreach ($serviceIds as $index => $serviceId) {
            $slotCount = isset($serviceCounts[$index]) ? intval($serviceCounts[$index]) : 1;
            for ($i = 0; $i < $slotCount; $i++) {
                $requests[] = [
                    'id' => $serviceId,
                    'source' => \App::$source_name
                ];
            }
        }

        foreach ($officeIds as $officeId) {
            $providers[] = [
                'id' => $officeId,
                'source' => \App::$source_name
            ];
        }

        $freeSlots = ZmsApiClientService::getFreeTimeslots(new ProviderList($providers), new RequestList($requests), DateTimeFormatHelper::getInternalDateFromISO($date), DateTimeFormatHelper::getInternalDateFromISO($date)) ?? new ProcessList();
        $timestamps = self::processFreeSlots($freeSlots);
        if (!empty($timestamps['errors'])) {
            return $timestamps;
        }

        if ($groupByOffice) {
            return new AvailableAppointmentsByOffice($timestamps);
        }

        return new AvailableAppointments(array_values($timestamps)[0]);
    }

    private static function processFreeSlots(ProcessList $freeSlots): array
    {
        $errors = ValidationService::validateGetProcessFreeSlots($freeSlots);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        $currentTimestamp = time();
        $appointmentTimestamps = array_reduce(iterator_to_array($freeSlots), function ($timestamps, $slot) use ($currentTimestamp) {

            if (isset($slot->appointments) && is_iterable($slot->appointments)) {
                $providerId = (int) $slot->scope->provider->id;
                foreach ($slot->appointments as $appointment) {
                    if (isset($appointment->date)) {
                        $timestamp = (int) $appointment->date;
                        if ($timestamp > $currentTimestamp) {
                            $timestamps[$providerId][$timestamp] = true;
                        }
                    }
                }
            }
            return $timestamps;
        }, []);
        foreach ($appointmentTimestamps as $providerId => &$timestamps) {
            $timestamps = array_keys($timestamps);
            asort($timestamps);
        }

        $errors = ValidationService::validateGetProcessByIdTimestamps($appointmentTimestamps);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        return $appointmentTimestamps;
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
        $thinnedProcess = MapperService::processToThinnedProcess($process);
        $errors = ValidationService::validateGetProcessNotFound($process);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

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
            $thinnedScope = new ThinnedScope(id: (int) $process->scope->id, provider: $thinnedProvider, shortName: $process->scope->getShortName() ?? null, telephoneActivated: (bool) $process->scope->getTelephoneActivated() ?? false, telephoneRequired: (bool) $process->scope->getTelephoneRequired() ?? false, customTextfieldActivated: (bool) $process->scope->getCustomTextfieldActivated() ?? false, customTextfieldRequired: (bool) $process->scope->getCustomTextfieldRequired() ?? false, customTextfieldLabel: $process->scope->getCustomTextfieldLabel() ?? null, captchaActivatedRequired: (bool) $process->scope->getCaptchaActivatedRequired() ?? false, displayInfo: $process->scope->getDisplayInfo() ?? null);
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

    public static function sendCancelationEmail(Process $confirmedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::sendCancelationEmail($confirmedProcess);
        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }
        return $clientUpdateResult;
    }
}
