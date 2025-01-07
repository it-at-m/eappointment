<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;
use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\ProcessFreeSlots;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\Core\ExceptionService;
use BO\Zmscitizenapi\Services\Core\ZmsApiClientService;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Collection\RequestRelationList;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\ProcessList;

class ZmsApiFacadeService
{

    public static function getOffices(): OfficeList|array
    {
        try {
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
                $matchingScope = $scopeMap[$provider->source . '_' . $provider->id] ?? null;

                $offices[] = new Office(
                    id: (int) $provider->id,
                    name: $provider->displayName ?? $provider->name,
                    address: $provider->data['address'] ?? null,
                    geo: $provider->data['geo'] ?? null,
                    scope: $matchingScope ? new ThinnedScope(
                        id: (int) $matchingScope->id,
                        provider: MapperService::providerToThinnedProvider($provider),
                        shortName: $matchingScope->getShortName(),
                        telephoneActivated: (bool) $matchingScope->getTelephoneActivated(),
                        telephoneRequired: (bool) $matchingScope->getTelephoneRequired(),
                        customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(),
                        customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(),
                        customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(),
                        captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(),
                        displayInfo: $matchingScope->getDisplayInfo()
                    ) : null
                );
            }

            return new OfficeList($offices);
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getScopes(): ThinnedScopeList|array
    {
        try {
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
                        shortName: $matchingScope->getShortName(),
                        telephoneActivated: (bool) $matchingScope->getTelephoneActivated(),
                        telephoneRequired: (bool) $matchingScope->getTelephoneRequired(),
                        customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated(),
                        customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired(),
                        customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(),
                        captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired(),
                        displayInfo: $matchingScope->getDisplayInfo()
                    );
                }
            }

            return new ThinnedScopeList($scopesProjectionList);
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getServices(): ServiceList|array
    {
        try {
            $requestList = ZmsApiClientService::getServices() ?? new RequestList();
            $services = [];

            foreach ($requestList as $request) {
                $additionalData = $request->getAdditionalData();

                $services[] = new Service(
                    id: (int) $request->getId(),
                    name: $request->getName(),
                    maxQuantity: $additionalData['maxQuantity'] ?? 1
                );
            }

            return new ServiceList($services);
        } catch (\RuntimeException $e) {
            return ExceptionService::servicesNotFound();
        }
    }

    public static function getServicesAndOffices(): OfficeServiceAndRelationList|array
    {
        try {
            $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
            $requestList = ZmsApiClientService::getServices() ?? new RequestList();
            $relationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();

            $offices = MapperService::mapOfficesWithScope($providerList) ?? new OfficeList;
            $services = MapperService::mapServicesWithCombinations($requestList, $relationList) ?? new ServiceList();
            $relations = MapperService::mapRelations($relationList) ?? new OfficeServiceRelationList();

            return new OfficeServiceAndRelationList($offices, $services, $relations);
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            if (strpos($e->getMessage(), 'servicesNotFound') !== false) {
                return ExceptionService::servicesNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    /* Todo add method
     * getCombinableServicesByIds
     * 
     * 
     * 
     */

    public static function getScopeByOfficeId(int $officeId): ThinnedScope|array
    {
        try {
            $matchingScope = ZmsApiClientService::getScopesByProviderId(
                \App::$source_name,
                $officeId
            )->getIterator()->current();

            if (!$matchingScope instanceof Scope) {
                return ExceptionService::scopeNotFound();
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

            return new ThinnedScope(
                id: (int) $result['id'],
                provider: $result['provider'],
                shortName: $result['shortName'],
                telephoneActivated: $result['telephoneActivated'],
                telephoneRequired: $result['telephoneRequired'],
                customTextfieldActivated: $result['customTextfieldActivated'],
                customTextfieldRequired: $result['customTextfieldRequired'],
                customTextfieldLabel: $result['customTextfieldLabel'],
                captchaActivatedRequired: $result['captchaActivatedRequired'],
                displayInfo: $result['displayInfo']
            );
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    /* Todo add method
     * getOfficeById
     * 
     * 
     * 
     */

    public static function getOfficeListByServiceId(int $serviceId): OfficeList|array
    {
        try {

            $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
            $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();

            $providerMap = [];
            foreach ($providerList as $provider) {
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
                        address: $provider->address ?? null,
                        geo: $provider->geo ?? null,
                        scope: $scope
                    );
                }
            }

            $errors = ValidationService::validateOfficesNotFound($offices);
            if (is_array($errors) && !empty($errors['errors'])) {
                return $errors;
            }

            return new OfficeList($offices);

        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getScopeById(?int $scopeId): ThinnedScope|array
    {
        try {

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
                shortName: $matchingScope->getShortName() ?? null,
                telephoneActivated: (bool) $matchingScope->getTelephoneActivated() ?? null,
                telephoneRequired: (bool) $matchingScope->getTelephoneRequired() ?? null,
                customTextfieldActivated: (bool) $matchingScope->getCustomTextfieldActivated() ?? null,
                customTextfieldRequired: (bool) $matchingScope->getCustomTextfieldRequired() ?? null,
                customTextfieldLabel: $matchingScope->getCustomTextfieldLabel() ?? null,
                captchaActivatedRequired: (bool) $matchingScope->getCaptchaActivatedRequired() ?? null,
                displayInfo: $matchingScope->getDisplayInfo() ?? null
            );
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getServicesByOfficeId(int $officeId): ServiceList|array
    {
        try {
            $requestList = ZmsApiClientService::getServices() ?? new RequestList();
            $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();

            $requestMap = [];
            foreach ($requestList as $request) {
                $requestMap[$request->id] = $request;
            }

            $services = [];
            foreach ($requestRelationList as $relation) {
                if ((int) $relation->provider->id === $officeId) {
                    $requestId = $relation->request->id;

                    if (isset($requestMap[$requestId])) {
                        $request = $requestMap[$requestId];
                        $services[] = new Service(
                            id: (int) $request->id,
                            name: $request->name,
                            maxQuantity: $request->getAdditionalData()['maxQuantity'] ?? 1
                        );
                    }
                }
            }

            $errors = ValidationService::validateServicesNotFound($services);
            if (is_array($errors) && !empty($errors['errors'])) {
                return $errors;
            }

            return new ServiceList($services);
        } catch (\RuntimeException $e) {
            return ExceptionService::servicesNotFound();
        }
    }

    public static function getOfficesThatProvideService(int $serviceId): OfficeList|array
    {
        try {
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
                if (in_array($provider->id, $providerIds) && 
                    isset($provider->data['public']) && 
                    $provider->data['public'] === true
                ) {
                    $scope = self::getScopeByOfficeId((int) $provider->id);
                    if (!is_array($scope)) {
                        $offices[] = new Office(
                            id: (int) $provider->id,
                            name: $provider->displayName ?? $provider->name,
                            address: $provider->data['address'] ?? null,
                            geo: $provider->data['geo'] ?? null,
                            scope: $scope instanceof ThinnedScope ? $scope : null
                        );
                    }
                }
            }
    
            $errors = ValidationService::validateOfficesNotFound($offices);
            if (is_array($errors) && !empty($errors['errors'])) {
                return $errors;
            }
    
            return new OfficeList($offices);
    
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            if (strpos($e->getMessage(), 'scopeNotFound') !== false) {
                return ExceptionService::scopeNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getServicesProvidedAtOffice(int $officeId): RequestList|array
    {
        try {
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
        } catch (\RuntimeException $e) {
            return ExceptionService::servicesNotFound();
        }
    }

    public static function getBookableFreeDays(int $officeId, int $serviceId, array $serviceCounts, string $startDate, string $endDate): AvailableDays|array
    {
        try {
            $firstDay = DateTimeFormatHelper::getInternalDateFromISO($startDate);
            $lastDay = DateTimeFormatHelper::getInternalDateFromISO($endDate);

            $freeDays = ZmsApiClientService::getFreeDays(
                new ProviderList([['id' => $officeId, 'source' => \App::$source_name]]),
                new RequestList([
                    [
                        'id' => $serviceId,
                        'source' => \App::$source_name,
                        'slotCount' => $serviceCounts,
                    ]
                ]),
                $firstDay,
                $lastDay,
            ) ?? new Calendar();

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
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'noAppointmentsAtLocation') !== false) {
                return ExceptionService::noAppointmentsAtLocation();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getFreeAppointments(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        array $date
    ): ProcessList|array {
        try {
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

            return ZmsApiClientService::getFreeTimeslots(
                new ProviderList([$office]),
                new RequestList($requests),
                $date,
                $date
            );
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'noAppointmentsAtLocation') !== false) {
                return ExceptionService::noAppointmentsAtLocation();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getAvailableAppointments(
        ?string $date,
        ?int $officeId,
        ?array $serviceIds,
        ?array $serviceCounts
    ): AvailableAppointments|array {

        try {
            $requests = [];
            foreach ($serviceIds as $index => $serviceId) {
                $slotCount = isset($serviceCounts[$index]) ? intval($serviceCounts[$index]) : 1;
                for ($i = 0; $i < $slotCount; $i++) {
                    $requests[] = [
                        'id' => $serviceId,
                        'source' => \App::$source_name,
                        'slotCount' => 1,
                    ];
                }
            }

            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                new ProviderList([['id' => $officeId, 'source' => \App::$source_name]]),
                new RequestList($requests),
                DateTimeFormatHelper::getInternalDateFromISO($date),
                DateTimeFormatHelper::getInternalDateFromISO($date)
            ) ?? new ProcessList();

            $timestamps = self::processFreeSlots($freeSlots);
            if (!empty($timestamps['errors'])) {
                return $timestamps;
            }

            return isset($timestamps->toArray()['appointmentTimestamps'])
                ? new AvailableAppointments($timestamps->toArray()['appointmentTimestamps'])
                : new AvailableAppointments();

        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'noAppointmentsAtLocation') !== false) {
                return ExceptionService::noAppointmentsAtLocation();
            }
            return ExceptionService::internalError();
        }
    }

    private static function processFreeSlots(ProcessList $freeSlots): ProcessFreeSlots|array
    {

        $errors = ValidationService::validateGetProcessFreeSlots($freeSlots);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        $currentTimestamp = time();

        $appointmentTimestamps = array_reduce(
            iterator_to_array($freeSlots),
            function ($timestamps, $slot) use ($currentTimestamp) {
                if (isset($slot->appointments) && is_iterable($slot->appointments)) {
                    foreach ($slot->appointments as $appointment) {
                        if (isset($appointment->date)) {
                            $timestamp = (int) $appointment->date;
                            if ($timestamp > $currentTimestamp) {
                                $timestamps[$timestamp] = true;
                            }
                        }
                    }
                }
                return $timestamps;
            },
            []
        );

        $appointmentTimestamps = array_keys($appointmentTimestamps);
        sort($appointmentTimestamps);

        $errors = ValidationService::validateGetProcessByIdTimestamps($appointmentTimestamps);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }

        return new ProcessFreeSlots($appointmentTimestamps);
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): ThinnedProcess|array
    {
        try {
            $process = ZmsApiClientService::reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts);
            return MapperService::processToThinnedProcess($process);
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'processAlreadyExists') !== false) {
                return ExceptionService::processAlreadyExists();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            return ExceptionService::internalError();
        }
    }

    public static function getThinnedProcessById(?int $processId, ?string $authKey): ThinnedProcess|array
    {

        try {

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


                $thinnedScope = new ThinnedScope(
                    id: (int) $process->scope->id,
                    provider: $thinnedProvider,
                    shortName: $process->scope->getShortName() ?? null,
                    telephoneActivated: (bool) $process->scope->getTelephoneActivated() ?? false,
                    telephoneRequired: (bool) $process->scope->getTelephoneRequired() ?? false,
                    customTextfieldActivated: (bool) $process->scope->getCustomTextfieldActivated() ?? false,
                    customTextfieldRequired: (bool) $process->scope->getCustomTextfieldRequired() ?? false,
                    customTextfieldLabel: $process->scope->getCustomTextfieldLabel() ?? null,
                    captchaActivatedRequired: (bool) $process->scope->getCaptchaActivatedRequired() ?? false,
                    displayInfo: $process->scope->getDisplayInfo() ?? null
                );
            }

            $thinnedProcess->scope = $thinnedScope;

            return $thinnedProcess;

        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            if (strpos($e->getMessage(), 'officesNotFound') !== false) {
                return ExceptionService::officesNotFound();
            }
            return ExceptionService::internalError();
        }
    }

    public static function updateClientData(Process $reservedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::submitClientData($reservedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

    public static function preconfirmAppointment(Process $reservedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::preconfirmProcess($reservedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

    public static function confirmAppointment(Process $preconfirmedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::confirmProcess($preconfirmedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

    public static function cancelAppointment(Process $confirmedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::cancelAppointment($confirmedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

    public static function sendPreconfirmationEmail(Process $reservedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::sendPreconfirmationEmail($reservedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

    public static function sendConfirmationEmail(Process $preconfirmedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::sendConfirmationEmail($preconfirmedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

    public static function sendCancelationEmail(Process $confirmedProcess): Process|array
    {
        try {
            $clientUpdateResult = ZmsApiClientService::sendCancelationEmail($confirmedProcess);
            if (isset($clientUpdateResult['error'])) {
                return $clientUpdateResult;
            }
            return $clientUpdateResult;
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'tooManyAppointmentsWithSameMail') !== false) {
                return ExceptionService::tooManyAppointmentsWithSameMail();
            }
            if (strpos($e->getMessage(), 'emailIsRequired') !== false) {
                return ExceptionService::emailIsRequired();
            }
            if (strpos($e->getMessage(), 'telephoneIsRequired') !== false) {
                return ExceptionService::telephoneIsRequired();
            }
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
            }
            return ExceptionService::internalError();
        }
    }

}
