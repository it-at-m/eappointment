<?php

namespace BO\Zmscitizenapi\Services;

class ValidationService
{

    public static function validateServiceLocationCombination($officeId, array $serviceIds)
    {


        $availableServices = ZmsApiFacadeService::getServicesProvidedAtOffice($officeId);
        $availableServiceIds = array_map(function ($service) {
            return $service['id'];
        }, $availableServices);

        $invalidServiceIds = array_filter($serviceIds, function ($serviceId) use ($availableServiceIds) {
            return !in_array($serviceId, $availableServiceIds);
        });

        if (!empty($invalidServiceIds)) {
            return [
                'status' => 400,
                'errorCode' => 'invalidLocationAndServiceCombination',
                'errorMessage' => 'The provided service(s) do not exist at the given location.',
                'invalidServiceIds' => $invalidServiceIds,
                'locationId' => $officeId,
                'lastModified' => time() * 1000,
            ];
        }

        return [
            'status' => 200,
            'message' => 'Valid service-location combination.',
        ];
    }

    public static function validateBookableFreeDays($officeId, $serviceId, $startDate, $endDate, $serviceCounts)
    {
        $errors = [];
        if (!$startDate) {
            $errors[] = ['type' => 'field', 'msg' => 'startDate is required and must be a valid date', 'path' => 'startDate', 'location' => 'query'];
        }
        if (!$endDate) {
            $errors[] = ['type' => 'field', 'msg' => 'endDate is required and must be a valid date', 'path' => 'endDate', 'location' => 'query'];
        }
        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = ['type' => 'field', 'msg' => 'officeId should be a 32-bit integer', 'path' => 'officeId', 'location' => 'query'];
        }
        if (!$serviceId || !is_numeric($serviceId)) {
            $errors[] = ['type' => 'field', 'msg' => 'serviceId should be a 32-bit integer', 'path' => 'serviceId', 'location' => 'query'];
        }
        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceCounts))) {
            $errors[] = ['type' => 'field', 'msg' => 'serviceCount should be a comma-separated string of integers', 'path' => 'serviceCount', 'location' => 'query'];
        }

        return $errors;
    }

    public static function validateGetAppointment($processId, $authKey)
    {
        $errors = [];

        if (!$processId || !is_numeric($processId) || intval($processId) <= 0) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'processId should be a 32-bit integer',
                'path' => 'processId',
                'location' => 'query'
            ];
        }

        if (!$authKey || !is_string($authKey)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'authKey should be a string',
                'path' => 'authKey',
                'location' => 'query'
            ];
        }

        return $errors;
    }

    public static function validateGetAvailableAppointments($date, $officeId, $serviceIds, $serviceCounts)
    {
        $errors = [];
    
        if (!$date) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'date is required and must be a valid date',
                'path' => 'date',
                'location' => 'body'
            ];
        }
    
        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'officeId should be a 32-bit integer',
                'path' => 'officeId',
                'location' => 'body'
            ];
        }
    
        if (empty($serviceIds[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceIds))) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'serviceId should be a comma-separated string of integers',
                'path' => 'serviceId',
                'location' => 'body'
            ];
        }
    
        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', implode(',', $serviceCounts))) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'serviceCount should be a comma-separated string of integers',
                'path' => 'serviceCount',
                'location' => 'body'
            ];
        }
    
        return $errors;
    }

}