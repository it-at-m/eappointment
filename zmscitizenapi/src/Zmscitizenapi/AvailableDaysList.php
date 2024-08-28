<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsentities\Calendar as CalendarEntity;

class AvailableDaysList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $officeId = $queryParams['officeId'] ?? null;
        $serviceId = $queryParams['serviceId'] ?? null;
        $serviceCount = intval($queryParams['serviceCount'] ?? '1');
        $startDate = $queryParams['startDate'] ?? null;
        $endDate = $queryParams['endDate'] ?? null;

        if (!$officeId || !$serviceId || !$startDate || !$endDate) {
            $responseContent = [
                'availableDays' => [],
                'error' => 'Missing or invalid parameters'
            ];
            $response = $response->withStatus(400)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }

        try {
            $firstDay = $this->convertDateToDayMonthYear($startDate);
            $lastDay = $this->convertDateToDayMonthYear($endDate);

            $calendar = new CalendarEntity();
            $calendar->firstDay = $firstDay;
            $calendar->lastDay = $lastDay;
            $calendar->providers = [
                ['id' => $officeId, 'source' => 'dldb']
            ];
            $calendar->requests = [
                [
                    'id' => $serviceId,
                    'source' => 'dldb',
                    'slotCount' => $serviceCount
                ]
            ];

            try {
                $apiResponse = \App::$http->readPostResult('/calendar/', $calendar);
            } catch (\Exception $e) {
                error_log('Error in AvailableDaysList during API request: ' . $e->getMessage());
                $responseContent = [
                    'availableDays' => [],
                    'error' => 'API request failed: ' . $e->getMessage()
                ];
                $response = $response->withStatus(500)
                                     ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode($responseContent));
                return $response;
            }

            $calendarEntity = $apiResponse->getEntity();
            $daysCollection = $calendarEntity->days;
            $formattedDays = [];
            foreach ($daysCollection as $day) {
                $formattedDays[] = sprintf('%04d-%02d-%02d', $day->year, $day->month, $day->day);
            }

            if (empty($formattedDays)) {
                $responseContent = [
                    'availableDays' => [],
                    'errorCode' => 'noAppointmentForThisScope',
                    'errorMessage' => 'No available days found for the given criteria',
                ];
                $response = $response->withStatus(404)
                                     ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode($responseContent));
                return $response;
            }

            $responseContent = [
                'availableDays' => $formattedDays,
                'lastModified' => round(microtime(true) * 1000),
            ];
            $response = $response->withStatus(200)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        } catch (\Exception $e) {
            error_log('Error in AvailableDaysList: ' . $e->getMessage());
            $responseContent = [
                'availableDays' => [],
                'errorCode' => 'noAppointmentForThisScope',
                'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine',
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

