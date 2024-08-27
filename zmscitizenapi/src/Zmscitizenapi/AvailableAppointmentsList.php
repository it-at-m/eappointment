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
        $serviceIds = explode(',', $queryParams['serviceId'] ?? '');
        $serviceCounts = explode(',', $queryParams['serviceCount'] ?? '1');

        if (!$date || !$officeId || empty($serviceIds)) {
            $responseContent = [
                'appointmentTimestamps' => [],
                'error' => 'Missing or invalid parameters'
            ];
            $response = $response->withStatus(400)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }

        try {
            $calendar = new CalendarEntity();
            $calendar->firstDay = $this->convertDateToDayMonthYear($date);
            $calendar->lastDay = $this->convertDateToDayMonthYear($date);
            $calendar->providers = [
                ['id' => $officeId, 'source' => 'dldb']
            ];

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
                $freeSlots = \App::$http->readPostResult('/process/status/free/', $calendar)->getCollection();
            } catch (\Exception $e) {
                error_log('Error in AvailableAppointmentsList during API request: ' . $e->getMessage());
                $responseContent = [
                    'appointmentTimestamps' => [],
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar',
                    'lastModified' => round(microtime(true) * 1000)
                ];
                $response = $response->withStatus(500)
                                     ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode($responseContent));
                return $response;
            }

            if (empty($freeSlots)) {
                $responseContent = [
                    'appointmentTimestamps' => [],
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar',
                    'lastModified' => round(microtime(true) * 1000)
                ];
                $response = $response->withStatus(404)
                                     ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode($responseContent));
                return $response;
            }

            $currentTimestamp = time();

            $appointmentTimestamps = [];
            foreach ($freeSlots as $slot) {
                foreach ($slot->appointments as $appointment) {
                    $timestamp = (int)$appointment->date;

                    // Ensure the timestamp is unique and in the future
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
                $response = $response->withStatus(404)
                                     ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode($responseContent));
                return $response;
            }

            sort($appointmentTimestamps);

            $responseContent = [
                'appointmentTimestamps' => $appointmentTimestamps,
                'lastModified' => round(microtime(true) * 1000)
            ];
            $response = $response->withStatus(200)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent, JSON_NUMERIC_CHECK));
            return $response;

        } catch (\Exception $e) {
            error_log('Error in AvailableAppointmentsList: ' . $e->getMessage());
            $responseContent = [
                'appointmentTimestamps' => [],
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar',
                'lastModified' => round(microtime(true) * 1000)
            ];
            $response = $response->withStatus(500)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }
    }

    private function convertDateToDayMonthYear($dateString)
    {
        $date = new \DateTime($dateString);
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }
}
