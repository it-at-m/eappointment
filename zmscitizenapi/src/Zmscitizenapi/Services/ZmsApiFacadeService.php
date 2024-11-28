<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Helper\UtilityHelper;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Scope;
use \BO\Zmsentities\Collection\ScopeList;

class ZmsApiFacadeService
{

    public static function getOffices(): array
    {
        $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
        $providerProjectionList = [];
    
        foreach (ZmsApiClientService::getOffices() as $provider) {
            $matchingScope = $scopeList->withProviderID($provider->source, $provider->id)->getIterator()->current();
    
            $providerData = [
                "id" => $provider->id,
                "name" => $provider->displayName ?? $provider->name,
            ];
    
            if ($matchingScope instanceof Scope) {
                $providerData["scope"] = [
                    "id" => $matchingScope->id,
                    "provider" => $matchingScope->provider,
                    "shortName" => $matchingScope->shortName,
                    "telephoneActivated" => $matchingScope->getTelephoneActivated(),
                    "telephoneRequired" => $matchingScope->getTelephoneRequired(),
                    "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated(),
                    "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired(),
                    "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel(),
                    "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired(),
                    "displayInfo" => $matchingScope->getDisplayInfo()
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
                "provider" => $scope->getProvider(),
                "shortName" => $scope->shortName,
                "telephoneActivated" => $scope->getTelephoneActivated(),
                "telephoneRequired" => $scope->getTelephoneRequired(),
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated(),
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired(),
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel(),
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired(),
                "displayInfo" => $scope->getDisplayInfo()
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

    public static function getScopeForProvider(?int $providerId, ?ScopeList $scopes): array
    {
        $filteredScopes = $scopes->withProviderID(\App::$source_name, (string)$providerId);
    
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
                "shortName" => $scope->shortName,
                "telephoneActivated" => $scope->getTelephoneActivated(),
                "telephoneRequired" => $scope->getTelephoneRequired(),
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated(),
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired(),
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel(),
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired(),
                "displayInfo" => $scope->getDisplayInfo(),
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

     public static function getScopeByOfficeId($officeId): array
     {
         $scopeList = new ScopeList(ZmsApiClientService::getScopes() ?? []);
         $matchingScope = $scopeList->withProviderID('dldb', $officeId)->getIterator()->current();
     
         if ($matchingScope instanceof Scope) {
             return [
                 "id" => $matchingScope->id,
                 "provider" => $matchingScope->provider,
                 "shortName" => $matchingScope->shortName,
                 "telephoneActivated" => $matchingScope->getTelephoneActivated(),
                 "telephoneRequired" => $matchingScope->getTelephoneRequired(),
                 "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated(),
                 "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired(),
                 "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel(),
                 "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired(),
                 "displayInfo" => $matchingScope->getDisplayInfo(),
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
                "provider" => $scope->getProvider(),
                "shortName" => $scope->shortName,
                "telephoneActivated" => $scope->getTelephoneActivated(),
                "telephoneRequired" => $scope->getTelephoneRequired(),
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated(),
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired(),
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel(),
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired(),
                "displayInfo" => $scope->getDisplayInfo(),
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
            return $relation->provider->id === $officeId;
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
                [['id' => $officeId, 'source' => 'dldb']],
                [
                    [
                        'id' => $serviceId,
                        'source' => 'dldb',
                        'slotCount' => $serviceCounts,
                    ]
                ],
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
    public static function getFreeAppointments(array $params): array
    {
        $office = [
            'id' => $params['officeId'],
            'source' => 'dldb'
        ];

        $requests = [];

        foreach ($params['serviceIds'] as $index => $serviceId) {
            $service = [
                'id' => $serviceId,
                'source' => 'dldb',
                'slotCount' => $params['serviceCounts'][$index]
            ];
            $requests = array_merge($requests, array_fill(0, $service['slotCount'], $service));
        }

        try {

            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                [$office],
                $requests,
                $params['date'],
                $params['date']
            );

            $psr7Response = $freeSlots->getResponse();
            $responseBody = (string) $psr7Response->getBody();
            $responseBody = json_decode($responseBody, true);

            return $responseBody['data'];

        } catch (\Exception $e) {
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An error occurred while fetching available appointments',
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
                        'source' => 'dldb',
                        'slotCount' => 1,
                    ];
                }
            }

            $freeSlots = ZmsApiClientService::getFreeTimeslots(
                [['id' => $officeId, 'source' => 'dldb']],
                $requests,
                UtilityHelper::getInternalDateFromISO($date),
                UtilityHelper::getInternalDateFromISO($date)
            );

            if (!$freeSlots || !method_exists($freeSlots, 'getCollection')) {
                throw new \Exception('Invalid response from API');
            }

            return self::processFreeSlots($freeSlots->getCollection());

        } catch (\Exception $e) {
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An error occurred while fetching available appointments',
                'status' => 500,
            ];
        }
    }

    private static function processFreeSlots($freeSlots): array
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

    public static function reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts): Process
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

    public static function updateClientData($reservedProcess): Process|array
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


