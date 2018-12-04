<?php
/**
 * Return availability list by scope in month view.
 *
 * @copyright 2018 BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Collection\AvailabilityList;

class ScopeAvailabilityMonth extends BaseController
{
    /**
     * Return response.
     *
     * @SuppressWarnings(Param)
     *
     * @param \Psr\Http\Message\RequestInterface  $request  The request instance
     * @param \Psr\Http\Message\ResponseInterface $response The response instance
     * @param array                               $args     The path arguments
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
        $firstDay = $dateTime->modify('first day of this month');
        $lastDay = $dateTime->modify('last day of this month');
        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/'.$scopeId.'/', ['resolveReferences' => 1])->getEntity();
        try {
            $availabilityList = \App::$http->readGetResult(
                '/scope/'.$scopeId.'/availability/',
                [
                    'resolveReferences' => 0,
                    'startDate' => $firstDay,
                    'endDate' => $lastDay,
                ]
            )
            ->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        }
        $availabilityList = $availabilityList->withScope($scope);
        $calendar = new Calendar();
        $calendar->firstDay->setDateTime($firstDay);
        $calendar->lastDay->setDateTime($lastDay);
        $calendar->scopes[] = $scope;
        try {
            $calendar = \App::$http->readPostResult('/calendar/', $calendar, ['fillWithEmptyDays' => 1])->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
        }
        $month = $calendar->getMonthList()->getFirst();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/availabilityMonth.twig',
            array(
                'availabilityList' => $availabilityList,
                //'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
                //'conflicts' => $conflicts,
                'calendar' => $calendar,
                'dayoffList' => $scope->getDayoffList(),
                'dateTime' => $dateTime,
                'timestamp' => $dateTime->getTimeStamp(),
                'month' => $month,
                'scope' => $scope,
                'menuActive' => 'owner',
                'title' => 'Behörden und Standorte - Öffnungszeiten',
                //'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
                'today' => $dateTime->format('Y-m-d'),
                'workstation' => $workstation,
                'baseMonthString' => $firstDay->format('m'),
                'baseYearString' => $lastDay->format('Y'),
                'baseMonth_timestamp' => $firstDay->getTimeStamp(),
            )
        );
    }
}
