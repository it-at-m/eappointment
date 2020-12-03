<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Collection\AvailabilityList;
use BO\Zmsentities\Collection\ProcessList;

class ScopeAvailabilityMonth extends BaseController
{
    /**
     * Return response.
     *
     * @SuppressWarnings(Param)
     *
     * @return string
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $dateTime = (isset($args['date'])) ? new \BO\Zmsentities\Helper\DateTime($args['date']) : \App::$now;
        $startDate = $dateTime->modify('first day of this month');
        $endDate = $dateTime->modify('last day of this month');

        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/', ['resolveReferences' => 1])->getEntity();
        $calendar = $this->getCalendar($scope, $startDate, $endDate);

        $availabilityList = $this->getAvailabilityList($scope, $startDate, $endDate);
        $processConflictList = \App::$http
            ->readGetResult('/scope/' . $scope->getId() . '/conflict/', [
                'startDate' => \App::$now->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ])
            ->getCollection();
        $processConflictList = $processConflictList ?
            $processConflictList->toConflictListByDay() :
            new \BO\Zmsentities\Collection\ProcessList();
        
        return \BO\Slim\Render::withHtml(
            $response,
            'page/availabilityMonth.twig',
            array(
                'availabilityList' => $availabilityList,
                'conflicts' => $processConflictList,
                'calendar' => $calendar,
                'dayoffList' => $scope->getDayoffList(),
                'dateTime' => $dateTime,
                'timestamp' => $dateTime->getTimeStamp(),
                'month' => $calendar->getMonthList()->getFirst(),
                'scope' => $scope,
                'menuActive' => 'owner',
                'title' => 'Behörden und Standorte - Öffnungszeiten',
                'today' => $dateTime->format('Y-m-d'),
                'workstation' => $workstation,
                'baseMonthString' => $startDate->format('m'),
                'baseYearString' => $endDate->format('Y'),
                'baseMonth_timestamp' => $startDate->getTimeStamp(),
            )
        );
    }

    protected function getAvailabilityList($scope, $startDate, $endDate)
    {
        try {
            $availabilityList = \App::$http
                ->readGetResult(
                    '/scope/' . $scope->getId() . '/availability/',
                    [
                        'startDate' => $startDate->format('Y-m-d'),
                        'endDate' => $endDate->format('Y-m-d')
                    ]
                )
                ->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        }
        return $availabilityList;
    }

    protected function getCalendar($scope, $startDate, $endDate)
    {
        $calendar = new Calendar();
        $calendar->firstDay->setDateTime($startDate);
        $calendar->lastDay->setDateTime($endDate);
        $calendar->scopes[] = $scope;
        try {
            $calendar = \App::$http->readPostResult('/calendar/', $calendar, ['fillWithEmptyDays' => 1])->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
        }
        return $calendar;
    }
}
