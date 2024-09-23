<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmsentities\Calendar as CalendarEntity;

class AvailableAppointmentsService
{
    public function getAvailableAppointments(array $queryParams)
    {
        $date = $queryParams['date'] ?? null;
        $officeId = $queryParams['officeId'] ?? null;
        $serviceIds = isset($queryParams['serviceId']) ? explode(',', $queryParams['serviceId']) : [];
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];

        $errors = $this->validateQueryParams($date, $officeId, $serviceIds, $serviceCounts);

        if (!empty($errors)) {
            return ['errors' => $errors, 'status' => 400];
        }

        try {
            $utilityHelper = new \BO\Zmscitizenapi\Helper\UtilityHelper();

            $calendar = new CalendarEntity();
            $calendar->firstDay = $utilityHelper->getInternalDateFromISO($date);
            $calendar->lastDay = $utilityHelper->getInternalDateFromISO($date);
            $calendar->providers = [['id' => $officeId, 'source' => 'dldb']];

            $calendar->requests = [];
            foreach ($serviceIds as $index => $serviceId) {
                $slotCount = isset($serviceCounts[$index]) ? intval($serviceCounts[$index]) : 1;
                for ($i = 0; $i < $slotCount; $i++) {
                    $calendar->requests[] = [
                        'id' => $serviceId,
                        'source' => 'dldb',
                        'slotCount' => 1,
                    ];
                }
            }

            $freeSlots = \App::$http->readPostResult('/process/status/free/', $calendar);
            if (!$freeSlots || !method_exists($freeSlots, 'getCollection')) {
                throw new \Exception('Invalid response from API');
            }

            return $this->processFreeSlots($freeSlots->getCollection());

        } catch (\Exception $e) {
            error_log('Error in AvailableAppointmentsService: ' . $e->getMessage());
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An error occurred while fetching available appointments',
                'status' => 500,
            ];
        }
    }

    public function getFreeAppointments(array $params)
    {
        $office = [
            'id' => $params['officeId'],
            'source' => 'dldb'
        ];

        $requests = [];

        // Loop through service IDs and service counts
        foreach ($params['serviceIds'] as $index => $serviceId) {
            $service = [
                'id' => $serviceId,
                'source' => 'dldb',
                'slotCount' => $params['serviceCounts'][$index]
            ];
            $requests = array_merge($requests, array_fill(0, $service['slotCount'], $service));
        }

        try {
            $processService = new ProcessService(\App::$http);

            $freeSlots = $processService->getFreeTimeslots(
                [$office],
                $requests,
                $params['date'],
                $params['date']
            );

            return $freeSlots['data'];

        } catch (\Exception $e) {
            error_log('Error in AvailableAppointmentsService: ' . $e->getMessage());
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An error occurred while fetching available appointments',
                'status' => 500,
            ];
        }
    }

    private function validateQueryParams($date, $officeId, $serviceIds, $serviceCounts)
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
    

    private function processFreeSlots($freeSlots)
    {
        if (empty($freeSlots) || !is_iterable($freeSlots)) {
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                'status' => 404,
            ];
        }
    
        $currentTimestamp = time();
        $appointmentTimestamps = [];
    
        foreach ($freeSlots as $slot) {

            if (!isset($slot->appointments) || !is_iterable($slot->appointments)) {
                continue;
            }
    
            foreach ($slot->appointments as $appointment) {
                if (isset($appointment->date)) {
                    $timestamp = (int)$appointment->date;
    
                    if (!in_array($timestamp, $appointmentTimestamps) && $timestamp > $currentTimestamp) {
                        $appointmentTimestamps[] = $timestamp;
                    }
                }
            }
        }
        if (empty($appointmentTimestamps)) {
            return [
                'appointmentTimestamps' => [],
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                'status' => 404,
            ];
        }
    
        // Sort the timestamps and return the response
        sort($appointmentTimestamps);
    
        return [
            'appointmentTimestamps' => $appointmentTimestamps,
            'lastModified' => round(microtime(true) * 1000),
            'status' => 200,
        ];
    }
    
    
    

}
