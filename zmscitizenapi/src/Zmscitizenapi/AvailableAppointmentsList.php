<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsentities\Calendar as CalendarEntity;

class AvailableAppointmentsList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $date = $queryParams['date'] ?? null;
        $officeId = $queryParams['officeId'] ?? null;
        $serviceIds = isset($queryParams['serviceId']) ? explode(',', $queryParams['serviceId']) : [];
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];
    
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
    
        if (empty($serviceIds[0]) || !preg_match('/^\d+(,\d+)*$/', $queryParams['serviceId'] ?? '')) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'serviceId should be a comma-separated string of integers',
                'path' => 'serviceId',
                'location' => 'body'
            ];
        }
    
        if (empty($serviceCounts[0]) || !preg_match('/^\d+(,\d+)*$/', $queryParams['serviceCount'] ?? '')) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'serviceCount should be a comma-separated string of integers',
                'path' => 'serviceCount',
                'location' => 'body'
            ];
        }
        if (!empty($errors)) {
            $responseContent = ['errors' => $errors];
            return $this->createJsonResponse($response, $responseContent, 400);
        }
    
        try {
            $calendar = new CalendarEntity();
            $calendar->firstDay = $this->convertDateToDayMonthYear($date);
            $calendar->lastDay = $this->convertDateToDayMonthYear($date);
            $calendar->providers = [['id' => $officeId, 'source' => 'dldb']];
    
            $calendar->requests = [];
            foreach ($serviceIds as $index => $serviceId) {
                $slotCount = isset($serviceCounts[$index]) ? intval($serviceCounts[$index]) : 1;
                for ($i = 0; $i < $slotCount; $i++) {
                    $calendar->requests[] = [
                        'id' => $serviceId,
                        'source' => 'dldb',
                        'slotCount' => 1
                    ];
                }
            }
    
            try {
                $freeSlots = \App::$http->readPostResult('/process/status/free/', $calendar);
                if (!$freeSlots || !method_exists($freeSlots, 'getCollection')) {
                    throw new \Exception('Invalid response from API');
                }
                $freeSlots = $freeSlots->getCollection();
            } catch (\Exception $e) {
                error_log('Error in AvailableAppointmentsList during API request: ' . $e->getMessage());
                $responseContent = [
                    'appointmentTimestamps' => [],
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar',
                    'lastModified' => round(microtime(true) * 1000)
                ];
                return $this->createJsonResponse($response, $responseContent, 500);
            }
    
            if (empty($freeSlots)) {
                $responseContent = [
                    'appointmentTimestamps' => [],
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar',
                    'lastModified' => round(microtime(true) * 1000)
                ];
                return $this->createJsonResponse($response, $responseContent, 404);
            }
    
            $currentTimestamp = time();
    
            $appointmentTimestamps = [];
            foreach ($freeSlots as $slot) {
                foreach ($slot->appointments as $appointment) {
                    $timestamp = (int)$appointment->date;
    
                    if (!in_array($timestamp, $appointmentTimestamps) && $timestamp > $currentTimestamp) {
                        $appointmentTimestamps[] = $timestamp;
                    }
                }
            }
    
            if (empty($appointmentTimestamps)) {
                $responseContent = [
                    'appointmentTimestamps' => [],
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar',
                    'lastModified' => round(microtime(true) * 1000)
                ];
                return $this->createJsonResponse($response, $responseContent, 404);
            }
    
            sort($appointmentTimestamps);
    
            $responseContent = [
                'appointmentTimestamps' => $appointmentTimestamps,
                'lastModified' => round(microtime(true) * 1000)
            ];
            return $this->createJsonResponse($response, $responseContent, 200);
    
        } catch (\Exception $e) {
            error_log('Error in AvailableAppointmentsList: ' . $e->getMessage());
            $responseContent = [
                'appointmentTimestamps' => [],
                'errorCode' => 'noAppointmentForThisScope',
                'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine',
                'lastModified' => round(microtime(true) * 1000)
            ];
            return $this->createJsonResponse($response, $responseContent, 500);
        }
    }

    private function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content, JSON_NUMERIC_CHECK));
        return $response;
    }
}
