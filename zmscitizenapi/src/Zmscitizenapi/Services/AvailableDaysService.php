<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmsentities\Calendar as CalendarEntity;

class AvailableDaysService
{
    public function getAvailableDays(array $queryParams)
    {
        $officeId = $queryParams['officeId'] ?? null;
        $serviceId = $queryParams['serviceId'] ?? null;
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];
        $startDate = $queryParams['startDate'] ?? null;
        $endDate = $queryParams['endDate'] ?? null;

        $errors = $this->validateQueryParams($officeId, $serviceId, $startDate, $endDate, $serviceCounts);

        if (!empty($errors)) {
            return ['errors' => $errors, 'status' => 400];
        }

        try {
            $firstDay = $this->getInternalDateFromISO($startDate);
            $lastDay = $this->getInternalDateFromISO($endDate);

            $calendar = new CalendarEntity();
            $calendar->firstDay = $firstDay;
            $calendar->lastDay = $lastDay;
            $calendar->providers = [['id' => $officeId, 'source' => 'dldb']];
            $calendar->requests = [
                [
                    'id' => $serviceId,
                    'source' => 'dldb',
                    'slotCount' => $serviceCounts,
                ]
            ];

            $apiResponse = \App::$http->readPostResult('/calendar/', $calendar);
            $calendarEntity = $apiResponse->getEntity();
            $daysCollection = $calendarEntity->days;
            $formattedDays = [];

            foreach ($daysCollection as $day) {
                $formattedDays[] = sprintf('%04d-%02d-%02d', $day->year, $day->month, $day->day);
            }

            if (empty($formattedDays)) {
                return [
                    'availableDays' => [],
                    'errorCode' => 'noAppointmentForThisScope',
                    'errorMessage' => 'No available days found for the given criteria',
                    'status' => 404,
                ];
            }

            return [
                'availableDays' => $formattedDays,
                'lastModified' => round(microtime(true) * 1000),
                'status' => 200,
            ];

        } catch (\Exception $e) {
            error_log('Error in AvailableDaysService: ' . $e->getMessage());
            return [
                'availableDays' => [],
                'errorCode' => 'internalError',
                'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine',
                'lastModified' => round(microtime(true) * 1000),
                'status' => 500,
            ];
        }
    }

    private function validateQueryParams($officeId, $serviceId, $startDate, $endDate, $serviceCounts)
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

    private function getInternalDateFromISO($dateString)
    {
        $date = new \DateTime($dateString);
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }
}
