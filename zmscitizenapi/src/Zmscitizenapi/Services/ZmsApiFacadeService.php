<?php

namespace BO\Zmscitizenapi\Services;

use \BO\Zmscitizenapi\Helper\UtilityHelper;
use \BO\Zmscitizenapi\Services\ZmsApiClientService;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Scope;
use \BO\Zmsentities\Collection\ScopeList;
use \BO\Zmsentities\Collection\ProviderList;
use \BO\Zmsentities\Collection\RequestList;
use \BO\Zmsentities\Collection\ProcessList;

class ZmsApiFacadeService
{

    public static function getOffices(): array
    {
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $providerProjectionList = [];

        foreach (ZmsApiClientService::getOffices() as $provider) {
            $matchingScope = $scopeList->withProviderID($provider->source, $provider->id)->getIterator()->current();

            $providerData = array_merge(
                [
                    "id" => $provider->id,
                    "name" => $provider->displayName ?? $provider->name,
                ],
                !empty($provider->data['address']) ? ["address" => $provider->data['address']] : [],
                !empty($provider->data['geo']) ? ["geo" => $provider->data['geo']] : []
            );
            
            if ($matchingScope instanceof Scope) {
                $providerData["scope"] = [
                    "id" => $matchingScope->id,
                    "provider" => $matchingScope->getProvider() ?? null,
                    "shortName" => $matchingScope->getShortName() ?? null,
                    "telephoneActivated" => $matchingScope->getTelephoneActivated() ?? null,
                    "telephoneRequired" => $matchingScope->getTelephoneRequired() ?? null,
                    "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated() ?? null,
                    "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired() ?? null,
                    "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel() ?? null,
                    "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired() ?? null,
                    "displayInfo" => $matchingScope->getDisplayInfo() ?? null
                ];
            }

            $providerProjectionList[] = $providerData;
        }

        return [
            "offices" => $providerProjectionList,
            "status" => 200
        ];
    }

    public static function getScopes(): array
    {
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $scopesProjectionList = [];

        foreach ($scopeList as $scope) {
            $scopesProjectionList[] = [
                "id" => $scope->id,
                "provider" => $scope->getProvider() ?? null,
                "shortName" => $scope->getShortName() ?? null,
                "telephoneActivated" => $scope->getTelephoneActivated() ?? null,
                "telephoneRequired" => $scope->getTelephoneRequired() ?? null,
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated() ?? null,
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired() ?? null,
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel() ?? null,
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired() ?? null,
                "displayInfo" => $scope->getDisplayInfo() ?? null
            ];
        }

        return [
            "scopes" => $scopesProjectionList,
            "status" => 200
        ];
    }

    public static function getServices(): array
    {

        $requestList = ZmsApiClientService::getServices() ?? [];
        $servicesProjectionList = [];

        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            $servicesProjectionList[] = [
                "id" => $request->getId(),
                "name" => $request->getName(),
                "maxQuantity" => $additionalData['maxQuantity'] ?? 1,
            ];
        }

        return [
            "services" => $servicesProjectionList,
            "status" => 200
        ];
    }

    public static function getScopeForProvider(int $providerId, ?ScopeList $scopes): array
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
                "provider" => $scope->getProvider(),
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

        return [
            'scope' => $result,
            'status' => 200,
        ];
    }

    public static function getServicesAndOffices(): array
    {
        $providerList = ZmsApiClientService::getOffices() ?? [];
        $requestList = ZmsApiClientService::getServices() ?? [];
        $relationList = ZmsApiClientService::getRequestRelationList() ?? [];

        $offices = MapperService::mapOfficesWithScope($providerList);
        $services = MapperService::mapServicesWithCombinations($requestList, $relationList);
        $relations = MapperService::mapRelations($relationList);

        return [
            "offices" => $offices,
            "services" => $services,
            "relations" => $relations,
            "status" => 200
        ];
    }

    /* Todo add method
     * getCombinableServicesByIds
     * 
     * 
     * 
     */

    public static function getScopeByOfficeId(int $officeId): array
    {
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $matchingScope = $scopeList->withProviderID(\App::$source_name, $officeId)->getIterator()->current();

        if ($matchingScope instanceof Scope) {
            return [
                "id" => $matchingScope->id,
                "provider" => $matchingScope->getProvider() ?? null,
                "shortName" => $matchingScope->getShortName() ?? null,
                "telephoneActivated" => $matchingScope->getTelephoneActivated() ?? null,
                "telephoneRequired" => $matchingScope->getTelephoneRequired() ?? null,
                "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated() ?? null,
                "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired() ?? null,
                "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel() ?? null,
                "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired() ?? null,
                "displayInfo" => $matchingScope->getDisplayInfo() ?? null,
            ];
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

    public static function getOfficesByServiceIds(array $serviceIds): array
    {
        $serviceIds = array_unique($serviceIds);

        $errors = ValidationService::validateGetOfficesByServiceIds($serviceIds);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $providerList = ZmsApiClientService::getOffices();
        $requestRelationList = ZmsApiClientService::getRequestRelationList();

        $offices = [];
        $notFoundIds = [];
        $addedOfficeIds = [];

        foreach ($serviceIds as $serviceId) {
            $found = false;
            foreach ($requestRelationList as $relation) {
                if ($relation->request->id == $serviceId) {
                    if (!in_array($relation->provider->id, $addedOfficeIds)) {
                        foreach ($providerList as $provider) {
                            if ($provider->id == $relation->provider->id) {
                                $offices[] = [
                                    "id" => $provider->id,
                                    "name" => $provider->name,
                                ];
                                $addedOfficeIds[] = $provider->id;
                                $found = true;
                                break;
                            }
                        }
                    } else {
                        $found = true;
                    }
                }
            }
            if (!$found) {
                $notFoundIds[] = $serviceId;
            }
        }

        $errors = ValidationService::validateOfficesNotFound($offices);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $responseContent = ['offices' => $offices];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following serviceId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        return [
            'offices' => $responseContent,
            'status' => 200
        ];
    }

    public static function getScopeByIds(array $scopeIds): array
    {
        $scopeIds = array_unique($scopeIds);

        $errors = ValidationService::validateGetScopeByIds($scopeIds);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $filteredScopes = new ScopeList();

        foreach ($scopeList as $scope) {
            if (in_array($scope->id, $scopeIds)) {
                $filteredScopes->addEntity($scope);
            }
        }

        $errors = ValidationService::validateScopesNotFound($filteredScopes);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $scopes = [];
        foreach ($filteredScopes as $scope) {
            $scopes[] = [
                "id" => $scope->id,
                "provider" => $scope->getProvider() ?? null,
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

        return [
            'scopes' => $scopes,
            'status' => 200,
        ];
    }

    public static function getServicesByOfficeIds(array $officeIds): array
    {
        $officeIds = array_unique($officeIds);

        $errors = ValidationService::validateGetServicesByOfficeIds($officeIds);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $requestList = ZmsApiClientService::getServices() ?? [];
        $requestRelationList = ZmsApiClientService::getRequestRelationList();

        $services = [];
        $notFoundIds = [];
        $addedServices = [];

        foreach ($officeIds as $officeId) {
            $found = false;
            foreach ($requestRelationList as $relation) {
                if ($relation->provider->id == $officeId) {
                    foreach ($requestList as $request) {
                        if ($request->id == $relation->request->id && !in_array($request->id, $addedServices)) {
                            $services[] = [
                                "id" => $request->id,
                                "name" => $request->name,
                                "maxQuantity" => $request->getAdditionalData()['maxQuantity'] ?? 1,
                            ];
                            $addedServices[] = $request->id;
                            $found = true;
                        }
                    }
                }
            }
            if (!$found) {
                $notFoundIds[] = $officeId;
            }
        }

        $errors = ValidationService::validateServicesNotFound($services);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        $responseContent = ['services' => $services];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following officeId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        return [
            'services' => $responseContent,
            'status' => 200,
        ];
    }

    /* Todo add method
     * getOfficesThatProvideService
     * 
     * 
     * 
     */

     public static function getServicesProvidedAtOffice(int $officeId): array
     {
         $requestRelationList = ZmsApiClientService::getRequestRelationList();
     
         $requestRelationArray = [];
         foreach ($requestRelationList as $relation) {
             $requestRelationArray[] = $relation;
         }
     
         $serviceIds = array_filter($requestRelationArray, function ($relation) use ($officeId) {
             return $relation->provider->id === $officeId || (string)$relation->provider->id === (string)$officeId;
         });
     
         $serviceIds = array_map(function ($relation) {
             return $relation->request->id;
         }, $serviceIds);
     
         $requestList = ZmsApiClientService::getServices() ?? [];
         $requestArray = [];
         foreach ($requestList as $request) {
             $requestArray[] = $request;
         }
     
         return array_filter($requestArray, function ($request) use ($serviceIds) {
             return in_array($request->id, $serviceIds);
         });
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
            $firstDay = UtilityHelper::getInternalDateFromISO($startDate);
            $lastDay = UtilityHelper::getInternalDateFromISO($endDate);

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
    
    public static function getFreeAppointments(array $params): ProcessList|array
    {
        $office = [
            'id' => $params['officeId'],
            'source' => \App::$source_name
        ];

        $requests = [];

        foreach ($params['serviceIds'] as $index => $serviceId) {
            $service = [
                'id' => $serviceId,
                'source' => \App::$source_name,
                'slotCount' => $params['serviceCounts'][$index]
            ];
            $requests = array_merge($requests, array_fill(0, $service['slotCount'], $service));
        }

        try {

            $freeSlots = new ProcessList();
            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                new ProviderList([$office]),
                new RequestList($requests),
                $params['date'],
                $params['date']
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

            $freeSlots = new ProcessList();
            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                new ProviderList([['id' => $officeId, 'source' => \App::$source_name]]),
                new RequestList($requests),
                UtilityHelper::getInternalDateFromISO($date),
                UtilityHelper::getInternalDateFromISO($date)
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

        return [
            'appointmentTimestamps' => $appointmentTimestamps,
            'status' => 200,
        ];
    }

    public static function reserveTimeslot(Process $appointmentProcess, array $serviceIds, array $serviceCounts): Process
    {
        return ZmsApiClientService::reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts);
    }

    public static function getProcessById(?int $processId, ?string $authKey): array
    {
        $errors = ValidationService::validateGetProcessById($processId, $authKey);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        try {
            $process = new Process;
            $process = ZmsApiClientService::getProcessById($processId, $authKey);

            $errors = ValidationService::validateGetProcessNotFound($process);
            if (!empty($errors['errors'])) {
                return $errors;
            }

            $responseData = UtilityHelper::getThinnedProcessData($process);
            return ['data' => $responseData, 'status' => 200];

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


