<?php

namespace BO\Zmscitizenapi;

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
        $serviceCounts = isset($queryParams['serviceCount']) ? explode(',', $queryParams['serviceCount']) : [];
        $startDate = $queryParams['startDate'] ?? null;
        $endDate = $queryParams['endDate'] ?? null;

        $errors = [];
        
        if (!$startDate) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'startDate is required and must be a valid date',
                'path' => 'startDate',
                'location' => 'query'
            ];
        }

        if (!$endDate) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'endDate is required and must be a valid date',
                'path' => 'endDate',
                'location' => 'query'
            ];
        }

        if (!$officeId || !is_numeric($officeId)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'officeId should be a 32-bit integer',
                'path' => 'officeId',
                'location' => 'query'
            ];
        }

        if (!$serviceId || !is_numeric($serviceId)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'serviceId should be a 32-bit integer',
                'path' => 'serviceId',
                'location' => 'query'
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

        if (empty($errors)) {
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
                        'slotCount' => $serviceCounts
                    ]
                ];

                try {
                    $apiResponse = \App::$http->readPostResult('/calendar/', $calendar);
                } catch (\Exception $e) {
                    error_log('Error in AvailableDaysList during API request: ' . $e->getMessage());
                    $responseContent = [
                        'availableDays' => [],
                        'errorCode' => 'apiRequestFailed',
                        'errorMessage' => 'API request failed: ' . $e->getMessage()
                    ];
                    return $this->createJsonResponse($response, $responseContent, 500);
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
                    return $this->createJsonResponse($response, $responseContent, 404);
                }

                $responseContent = [
                    'availableDays' => $formattedDays,
                    'lastModified' => round(microtime(true) * 1000),
                ];
                return $this->createJsonResponse($response, $responseContent, 200);

            } catch (\Exception $e) {
                error_log('Error in AvailableDaysList: ' . $e->getMessage());
                $responseContent = [
                    'availableDays' => [],
                    'errorCode' => 'internalError',
                    'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine',
                    'lastModified' => round(microtime(true) * 1000)
                ];
                return $this->createJsonResponse($response, $responseContent, 500);
            }
        } else {
            $responseContent = ['errors' => $errors];
            return $this->createJsonResponse($response, $responseContent, 400);
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
