<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;

use BO\Zmscitizenapi\Models\Office;
use BO\Zmscitizenapi\Models\Service;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\ZmsApiClientService;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\ProcessList;

class ZmsApiFacadeService
{

    public static function getOffices(): OfficeList
    {
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $providerList = ZmsApiClientService::getOffices();
        $offices = [];
    
        foreach ($providerList as $provider) {
            $matchingScope = $scopeList->withProviderID($provider->source, $provider->id)->getIterator()->current();
    
            $offices[] = new Office(
                id: (string)$provider->id,
                name: $provider->displayName ?? $provider->name,
                address: $provider->data['address'] ?? null,
                geo: $provider->data['geo'] ?? null,
                scope: $matchingScope ? new ThinnedScope(
                    id: $matchingScope->id,
                    provider: MapperService::providerToThinnedProvider($provider),
                    shortName: $matchingScope->getShortName(),
                    telephoneActivated: $matchingScope->getTelephoneActivated(),
                    telephoneRequired: $matchingScope->getTelephoneRequired(),
                    customTextfieldActivated: $matchingScope->getCustomTextfieldActivated(),
                    customTextfieldRequired: $matchingScope->getCustomTextfieldRequired(),
                    customTextfieldLabel: $matchingScope->getCustomTextfieldLabel(),
                    captchaActivatedRequired: $matchingScope->getCaptchaActivatedRequired(),
                    displayInfo: $matchingScope->getDisplayInfo()
                ) : null
            );
        }
    
        return new OfficeList($offices);
    }
    

    public static function getScopes(): ThinnedScopeList
    {
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $providerList = ZmsApiClientService::getOffices();
        $scopesProjectionList = [];
    
        foreach ($scopeList as $scope) {
            if (!$scope instanceof Scope) {
                throw new \InvalidArgumentException("Expected instance of Scope.");
            }
            
            $scopesProjectionList[] = new ThinnedScope(
                id: $scope->id,
                provider: MapperService::providerToThinnedProvider($scope->getProvider()),
                shortName: $scope->getShortName(),
                telephoneActivated: $scope->getTelephoneActivated(),
                telephoneRequired: $scope->getTelephoneRequired(),
                customTextfieldActivated: $scope->getCustomTextfieldActivated(),
                customTextfieldRequired: $scope->getCustomTextfieldRequired(),
                customTextfieldLabel: $scope->getCustomTextfieldLabel(),
                captchaActivatedRequired: $scope->getCaptchaActivatedRequired(),
                displayInfo: $scope->getDisplayInfo()
            );
        }
    
        return new ThinnedScopeList($scopesProjectionList);
    }     

    public static function getServices(): ServiceList
    {
        $requestList = ZmsApiClientService::getServices() ?? [];
        $services = [];
    
        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
    
            $services[] = new Service(
                id: (int)$request->getId(),
                name: $request->getName(),
                maxQuantity: $additionalData['maxQuantity'] ?? 1
            );
        }
    
        return new ServiceList($services);
    }

    public static function getScopeForProvider(int $providerId, ?ScopeList $scopes): ThinnedScope|array
    {
        $filteredScopes = $scopes->withProviderID(\App::$source_name, (string) $providerId);

        if ($filteredScopes->count() === 0) {
            $errors = ValidationService::validateScopesNotFound($filteredScopes);
            if (!empty($errors['errors'])) {
                return $errors;
            }
        }

        $result = [];
        foreach ($filteredScopes as $scope) {
            $result = [
                "id" => $scope->id,
                "provider" => MapperService::providerToThinnedProvider($scope->getProvider()),
                "shortName" => $scope->getShortName() ?? null,
                "telephoneActivated" => $scope->getTelephoneActivated() ?? null,
                "telephoneRequired" => $scope->getTelephoneRequired() ?? null,
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated() ?? null,
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired() ?? null,
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel() ?? null,
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired() ?? null,
                "displayInfo" => $scope->getDisplayInfo() ?? null,
            ];
        }       

        return new ThinnedScope(...$result);
    }

    public static function getServicesAndOffices(): OfficeServiceAndRelationList
    {
        $providerList = ZmsApiClientService::getOffices() ?? [];
        $requestList = ZmsApiClientService::getServices() ?? [];
        $relationList = ZmsApiClientService::getRequestRelationList() ?? [];
    
        $offices = MapperService::mapOfficesWithScope($providerList) ?? new OfficeList;
        $services = MapperService::mapServicesWithCombinations($requestList, $relationList) ?? new ServiceList();
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
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $matchingScope = $scopeList->withProviderID(\App::$source_name, $officeId)->getIterator()->current();

        if ($matchingScope instanceof Scope) {
            $result = [
                "id" => $matchingScope->id,
                "provider" => MapperService::providerToThinnedProvider($matchingScope->getProvider()) ?? null,
                "shortName" => $matchingScope->getShortName() ?? null,
                "telephoneActivated" => $matchingScope->getTelephoneActivated() ?? null,
                "telephoneRequired" => $matchingScope->getTelephoneRequired() ?? null,
                "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated() ?? null,
                "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired() ?? null,
                "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel() ?? null,
                "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired() ?? null,
                "displayInfo" => $matchingScope->getDisplayInfo() ?? null,
            ];

            return new ThinnedScope(...$result);
        }

        return [
            'error' => 'Scope not found for the provided office ID',
            'status' => 404,
        ];
    }

    /* Todo add method
     * getOfficeByIds
     * 
     * 
     * 
     */

     public static function getOfficesByServiceIds(array $serviceIds): OfficeList|array
     {
         $serviceIds = array_unique($serviceIds);
     
         $errors = ValidationService::validateGetOfficesByServiceIds($serviceIds);
         if (!empty($errors['errors'])) {
             return $errors;
         }

         $providerList = ZmsApiClientService::getOffices();
         $requestRelationList = ZmsApiClientService::getRequestRelationList();
     
         $offices = [];
         $addedOfficeIds = [];

         foreach ($serviceIds as $serviceId) {
             foreach ($requestRelationList as $relation) {
                 if ($relation->request->id == $serviceId) {
                     if (!in_array($relation->provider->id, $addedOfficeIds)) {
                         foreach ($providerList as $provider) {
                             if ($provider->id == $relation->provider->id) {
                                 $scope = null;
     
                                 $scopeData = self::getScopeByOfficeId($provider->id);
                                 if ($scopeData instanceof ThinnedScope) {
                                     $scope = $scopeData;
                                 }
     
                                 $offices[] = new Office(
                                     id: $provider->id,
                                     name: $provider->name,
                                     address: $provider->address ?? null,
                                     geo: $provider->geo ?? null,
                                     scope: $scope
                                 );
     
                                 $addedOfficeIds[] = $provider->id;
                                 break;
                             }
                         }
                     }
                 }
             }
         }
     
         $errors = ValidationService::validateOfficesNotFound($offices);
         if (!empty($errors['errors'])) {
             return $errors;
         }
     
         return new OfficeList($offices);
     }
     
     
    public static function getScopeById(?int $scopeId): ThinnedScope|array
    {
        $errors = ValidationService::validateGetScopeByIds($scopeId);
        if (!empty($errors['errors'])) {
            return $errors;
        }
    
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $filteredScopes = new ScopeList();

        foreach ($scopeList as $scope) {
            if ((int)$scope->id === $scopeId) {
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
                "telephoneActivated" => $scope->getTelephoneActivated() ?? null,
                "telephoneRequired" => $scope->getTelephoneRequired() ?? null,
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated() ?? null,
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired() ?? null,
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel() ?? null,
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired() ?? null,
                "displayInfo" => $scope->getDisplayInfo() ?? null,
            ];
        }
    
        return new ThinnedScope(...$result);
    }
    

    public static function getServicesByOfficeIds(array $officeIds): ServiceList|array
    {

        $officeIds = array_unique($officeIds);

        $errors = ValidationService::validateGetServicesByOfficeIds($officeIds);
        if (!empty($errors['errors'])) {
            return $errors;
        }
    
        $requestList = ZmsApiClientService::getServices() ?? [];
        $requestRelationList = ZmsApiClientService::getRequestRelationList();
    
        $services = [];
        $addedServiceIds = [];
    
        foreach ($officeIds as $officeId) {
            foreach ($requestRelationList as $relation) {
                if ($relation->provider->id == $officeId) {
                    foreach ($requestList as $request) {
                        if ($request->id == $relation->request->id && !in_array($request->id, $addedServiceIds)) {
                            $services[] = new Service(
                                id: (int)$request->id,
                                name: $request->name,
                                maxQuantity: $request->getAdditionalData()['maxQuantity'] ?? 1
                            );
                            $addedServiceIds[] = $request->id;
                        }
                    }
                }
            }
        }

        $errors = ValidationService::validateServicesNotFound($services);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return new ServiceList($services);
    }
    

    /* Todo add method
     * getOfficesThatProvideService
     * 
     * 
     * 
     */

    public static function getServicesProvidedAtOffice(int $officeId): RequestList
    {
        $requestRelationList = ZmsApiClientService::getRequestRelationList();

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

        $requestList = ZmsApiClientService::getServices();
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

    public static function getBookableFreeDays(array $queryParams): array
    {
        $officeId = $queryParams['officeId'] ?? null;
        $serviceId = $queryParams['serviceId'] ?? null;
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];
        $startDate = $queryParams['startDate'] ?? null;
        $endDate = $queryParams['endDate'] ?? null;

        $errors = ValidationService::validateGetBookableFreeDays($officeId, $serviceId, $startDate, $endDate, $serviceCounts);
        if (!empty($errors['errors'])) {
            return $errors;
        }

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
            );

            $daysCollection = $freeDays->days;

            //Typing
            $formattedDays = [];

            foreach ($daysCollection as $day) {
                $formattedDays[] = sprintf('%04d-%02d-%02d', $day->year, $day->month, $day->day);
            }

            $errors = ValidationService::validateAppointmentDaysNotFound($formattedDays);
            if (!empty($errors['errors'])) {
                return $errors;
            }

            return [
                'availableDays' => $formattedDays,
                'status' => 200,
            ];

        } catch (\Exception $e) {
            return ExceptionService::noAppointmentsAtLocation();
        }
    }

    public static function getFreeAppointments(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        array $date
    ): ProcessList|array {
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

        try {
            $freeSlots = new ProcessList();
            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                new ProviderList([$office]),
                new RequestList($requests),
                $date,
                $date
            );

            return $freeSlots;
        } catch (\Exception $e) {
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An error occurred while fetching available free appointments',
                'status' => 500,
            ];
        }
    }

    public static function getAvailableAppointments(array $queryParams): array
    {
        $date = $queryParams['date'] ?? null;
        $officeId = $queryParams['officeId'] ?? null;
        $serviceIds = isset($queryParams['serviceId']) ? explode(',', $queryParams['serviceId']) : [];
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];

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

            //Typing
            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                new ProviderList([['id' => $officeId, 'source' => \App::$source_name]]),
                new RequestList($requests),
                DateTimeFormatHelper::getInternalDateFromISO($date),
                DateTimeFormatHelper::getInternalDateFromISO($date)
            );

            return self::processFreeSlots($freeSlots);

        } catch (\Exception $e) {
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An error occurred while fetching available appointments',
                'status' => 500,
            ];
        }
    }

    private static function processFreeSlots(ProcessList $freeSlots): array
    {
        $errors = ValidationService::validateGetProcessFreeSlots($freeSlots);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $currentTimestamp = time();
        $appointmentTimestamps = [];

        foreach ($freeSlots as $slot) {
            if (!isset($slot->appointments) || !is_iterable($slot->appointments)) {
                continue;
            }

            foreach ($slot->appointments as $appointment) {

                if (isset($appointment->date)) {
                    $timestamp = (int) $appointment->date;

                    if (!in_array($timestamp, $appointmentTimestamps) && $timestamp > $currentTimestamp) {
                        $appointmentTimestamps[] = $timestamp;
                    }
                }
            }
        }

        $errors = ValidationService::validateGetProcessByIdTimestamps($appointmentTimestamps);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        sort($appointmentTimestamps);

        //Typing 
        return [
            'appointmentTimestamps' => $appointmentTimestamps,
            'status' => 200,
        ];
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): ThinnedProcess
    {
        return MapperService::processToThinnedProcess(ZmsApiClientService::reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts));
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

            $thinnedProcess = MapperService::processToThinnedProcess($process);

            return $thinnedProcess;

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'kein Termin gefunden') !== false) {
                return ExceptionService::appointmentNotFound();
            } else {
                throw $e;
            }
        }
    }

    public static function updateClientData(Process $reservedProcess): Process|array
    {
        $clientUpdateResult = ZmsApiClientService::submitClientData($reservedProcess);

        if (isset($clientUpdateResult['error'])) {
            return $clientUpdateResult;
        }

        return $clientUpdateResult;
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
