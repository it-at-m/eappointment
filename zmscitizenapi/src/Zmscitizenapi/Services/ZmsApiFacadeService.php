<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;
use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\ExceptionService;
use BO\Zmscitizenapi\Services\ZmsApiClientService;
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
            return ExceptionService::officesNotFound();
        }
    }

    public static function getScopes(): ThinnedScopeList|array
    {
        try {
            $scopeList = ZmsApiClientService::getScopes() ?? new ScopeList();
            $scopesProjectionList = [];

            foreach ($scopeList as $scope) {
                if (!$scope instanceof Scope) {
                    throw new \InvalidArgumentException("Expected instance of Scope.");
                }

                $scopesProjectionList[] = new ThinnedScope(
                    id: (int) $scope->id,
                    provider: MapperService::providerToThinnedProvider($scope->getProvider()),
                    shortName: $scope->getShortName(),
                    telephoneActivated: (bool) $scope->getTelephoneActivated(),
                    telephoneRequired: (bool) $scope->getTelephoneRequired(),
                    customTextfieldActivated: (bool) $scope->getCustomTextfieldActivated(),
                    customTextfieldRequired: (bool) $scope->getCustomTextfieldRequired(),
                    customTextfieldLabel: $scope->getCustomTextfieldLabel(),
                    captchaActivatedRequired: (bool) $scope->getCaptchaActivatedRequired(),
                    displayInfo: $scope->getDisplayInfo()
                );
            }

            return new ThinnedScopeList($scopesProjectionList);
        } catch (\RuntimeException $e) {
            return ExceptionService::scopesNotFound();
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
 
             if ($matchingScope instanceof Scope) {
                 $result = [
                     "id" => $matchingScope->id,
                     "provider" => MapperService::providerToThinnedProvider($matchingScope->getProvider()) ?? null,
                     "shortName" => $matchingScope->getShortName() ?? null,
                     "telephoneActivated" => (bool) $matchingScope->getTelephoneActivated() ?? null,
                     "telephoneRequired" => (bool) $matchingScope->getTelephoneRequired() ?? null,
                     "customTextfieldActivated" => (bool) $matchingScope->getCustomTextfieldActivated() ?? null,
                     "customTextfieldRequired" => (bool) $matchingScope->getCustomTextfieldRequired() ?? null,
                     "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel() ?? null,
                     "captchaActivatedRequired" => (bool) $matchingScope->getCaptchaActivatedRequired() ?? null,
                     "displayInfo" => $matchingScope->getDisplayInfo() ?? null,
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
             }
 
             return ExceptionService::scopeNotFound();
         } catch (\RuntimeException $e) {
             return ExceptionService::scopeNotFound();
         }
     }

    /* Todo add method
     * getOfficeByIds
     * 
     * 
     * 
     */

     public static function getOfficesByServiceIds(array $serviceIds): OfficeList|array
     {
         try {
             $serviceIds = array_unique($serviceIds);
 
             $errors = ValidationService::validateGetOfficesByServiceIds($serviceIds);
             if (!empty($errors['errors'])) {
                 return $errors;
             }
 
             $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
             $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
 
             $providerMap = [];
             foreach ($providerList as $provider) {
                 $providerMap[$provider->id] = $provider;
             }
 
             $relationMap = [];
             foreach ($requestRelationList as $relation) {
                 $requestId = $relation->request->id;
                 $providerId = $relation->provider->id;
                 $relationMap[$requestId][] = $providerId;
             }
 
             $addedOfficeIds = [];
             $offices = [];
 
             foreach ($serviceIds as $serviceId) {
                 if (!isset($relationMap[$serviceId])) {
                     continue;
                 }
 
                 foreach ($relationMap[$serviceId] as $providerId) {
                     if (isset($addedOfficeIds[$providerId]) || !isset($providerMap[$providerId])) {
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
 
                     $addedOfficeIds[$provider->id] = true;
                 }
             }
 
             $errors = ValidationService::validateOfficesNotFound($offices);
             if (!empty($errors['errors'])) {
                 return $errors;
             }
 
             return new OfficeList($offices);
         } catch (\RuntimeException $e) {
             return ExceptionService::officesNotFound();
         }
     }

     public static function getScopeById(?int $scopeId): ThinnedScope|array
     {
         try {
             $errors = ValidationService::validateGetScopeByIds($scopeId);
             if (!empty($errors['errors'])) {
                 return $errors;
             }
 
             $scopeList = ZmsApiClientService::getScopes() ?? new ScopeList();
             $filteredScopes = new ScopeList();
 
             foreach ($scopeList as $scope) {
                 if ((int) $scope->id === $scopeId) {
                     $filteredScopes->addEntity($scope);
                 }
             }
 
             $errors = ValidationService::validateScopesNotFound($filteredScopes);
             if (!empty($errors['errors'])) {
                 return $errors;
             }
 
             $result = [];
             foreach ($filteredScopes as $scope) {
                 $result = [
                     "id" => $scope->id,
                     "provider" => MapperService::providerToThinnedProvider($scope->getProvider()) ?? null,
                     "shortName" => $scope->getShortName() ?? null,
                     "telephoneActivated" => (bool) $scope->getTelephoneActivated() ?? null,
                     "telephoneRequired" => (bool) $scope->getTelephoneRequired() ?? null,
                     "customTextfieldActivated" => (bool) $scope->getCustomTextfieldActivated() ?? null,
                     "customTextfieldRequired" => (bool) $scope->getCustomTextfieldRequired() ?? null,
                     "customTextfieldLabel" => $scope->getCustomTextfieldLabel() ?? null,
                     "captchaActivatedRequired" => (bool) $scope->getCaptchaActivatedRequired() ?? null,
                     "displayInfo" => $scope->getDisplayInfo() ?? null,
                 ];
             }
 
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
             return ExceptionService::scopeNotFound();
         }
     }

     public static function getServicesByOfficeIds(array $officeIds): ServiceList|array
     {
         try {
             $officeIds = array_unique($officeIds);
 
             $errors = ValidationService::validateGetServicesByOfficeIds($officeIds);
             if (!empty($errors['errors'])) {
                 return $errors;
             }
 
             $requestList = ZmsApiClientService::getServices() ?? new RequestList();
             $requestRelationList = ZmsApiClientService::getRequestRelationList() ?? new RequestRelationList();
 
             $requestMap = [];
             foreach ($requestList as $request) {
                 $requestMap[$request->id] = $request;
             }
 
             $relationMap = [];
             foreach ($requestRelationList as $relation) {
                 $providerId = $relation->provider->id;
                 $requestId = $relation->request->id;
 
                 if (!isset($relationMap[$providerId])) {
                     $relationMap[$providerId] = [];
                 }
                 $relationMap[$providerId][] = $requestId;
             }
 
             $services = [];
             $addedServiceIds = [];
 
             foreach ($officeIds as $officeId) {
                 if (isset($relationMap[$officeId])) {
                     foreach ($relationMap[$officeId] as $requestId) {
                         if (!in_array($requestId, $addedServiceIds) && isset($requestMap[$requestId])) {
                             $request = $requestMap[$requestId];
                             $services[] = new Service(
                                 id: (int) $request->id,
                                 name: $request->name,
                                 maxQuantity: $request->getAdditionalData()['maxQuantity'] ?? 1
                             );
                             $addedServiceIds[] = $request->id;
                         }
                     }
                 }
             }
 
             $errors = ValidationService::validateServicesNotFound($services);
             if (!empty($errors['errors'])) {
                 return $errors;
             }
 
             return new ServiceList($services);
         } catch (\RuntimeException $e) {
             return ExceptionService::servicesNotFound();
         }
     }

    /* Todo add method
     * getOfficesThatProvideService
     * 
     * 
     * 
     */

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

     public static function getBookableFreeDays(array $queryParams): AvailableDays|array
     {
         $officeId = $queryParams['officeId'] ?? null;
         $serviceId = $queryParams['serviceId'] ?? null;
         $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];
         $startDate = $queryParams['startDate'] ?? null;
         $endDate = $queryParams['endDate'] ?? null;
 
         $errors = ValidationService::validateGetBookableFreeDays((int) $officeId, (int) $serviceId, $startDate, $endDate, $serviceCounts);
         if (!empty($errors['errors'])) {
             return $errors;
         }
 
         try {
             $firstDay = DateTimeFormatHelper::getInternalDateFromISO($startDate);
             $lastDay = DateTimeFormatHelper::getInternalDateFromISO($endDate);
 
             $freeDays = ZmsApiClientService::getFreeDays(
                 new ProviderList([['id' => $officeId, 'source' => \App::$source_name]]),
                 new RequestList([[
                     'id' => $serviceId,
                     'source' => \App::$source_name,
                     'slotCount' => $serviceCounts,
                 ]]),
                 $firstDay,
                 $lastDay,
             ) ?? new Calendar();
 
             $daysCollection = $freeDays->days;
             $formattedDays = [];
             foreach ($daysCollection as $day) {
                 $formattedDays[] = sprintf('%04d-%02d-%02d', $day->year, $day->month, $day->day);
             }
 
             $errors = ValidationService::validateAppointmentDaysNotFound($formattedDays);
             if (!empty($errors['errors'])) {
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
            ) ?? new ProcessList();
        } catch (\RuntimeException $e) {
            return ExceptionService::noAppointmentsAtLocation();
        }
    }

    public static function getAvailableAppointments(
        ?string $date,
        ?int $officeId,
        ?array $serviceIds,
        ?array $serviceCounts
    ): AvailableAppointments|array {
        $errors = ValidationService::validateGetAvailableAppointments($date, $officeId, $serviceIds, $serviceCounts);
        if (!empty($errors['errors'])) {
            return $errors;
        }

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
            if (isset($timestamps['appointmentTimestamps'])) {
                return new AvailableAppointments($timestamps['appointmentTimestamps']);
            }

            return $timestamps;
        } catch (\RuntimeException $e) {
            return ExceptionService::noAppointmentsAtLocation();
        }
    }

    private static function processFreeSlots(ProcessList $freeSlots): array
    {
        $errors = ValidationService::validateGetProcessFreeSlots($freeSlots);
        if (!empty($errors['errors'])) {
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
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return [
            'appointmentTimestamps' => $appointmentTimestamps
        ];
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
        $errors = ValidationService::validateGetProcessById($processId, $authKey);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        try {
            $process = ZmsApiClientService::getProcessById($processId, $authKey);
            $errors = ValidationService::validateGetProcessNotFound($process);
            if (!empty($errors['errors'])) {
                return $errors;
            }

            return MapperService::processToThinnedProcess($process);
        } catch (\RuntimeException $e) {
            if (strpos($e->getMessage(), 'appointmentNotFound') !== false) {
                return ExceptionService::appointmentNotFound();
            }
            if (strpos($e->getMessage(), 'authKeyMismatch') !== false) {
                return ExceptionService::authKeyMismatch();
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

    /* Todo add method
     * preconfirmAppointment
     * 
     * 
     * 
     */


    /* Todo add method
     * confirmAppointment
     * 
     * 
     * 
     */

    /* Todo add method
     * cancelAppointment
     * 
     * 
     * 
     */

}
