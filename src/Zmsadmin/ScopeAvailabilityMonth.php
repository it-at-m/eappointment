<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Availability;
use BO\Zmsentities\Calendar;
use BO\Zmsentities\Month;
use BO\Zmsentities\Collection\AvailabilityList;

/**
  * Handle requests concerning services
  *
  */
class ScopeAvailabilityMonth extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        if (isset($args['date'])) {
            $dateTime = new \BO\Zmsentities\Helper\DateTime($args['date']);
        } else {
            $dateTime = \App::$now;
        }
        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/', ['resolveReferences' => 1])->getEntity();
        $availabilityList = \App::$http->readGetResult(
            '/scope/'. $scopeId .'/availability/',
            ['resolveReferences' => 2]
        )->getCollection();
        $calendar = new Calendar();
        $calendar->firstDay->setDateTime($dateTime->modify('first day of this month'));
        $calendar->lastDay->setDateTime($dateTime->modify('last day of this month'));
        $calendar->scopes[] = $scope;
        try {
            $calendar = \App::$http->readPostResult('/calendar/', $calendar)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
            // TODO Berechne die Tage im Kalendar
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
                'menuActive' => 'availability',
                //'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
                'today' => $dateTime->format('Y-m-d'),
                'workstation' => $workstation,
                'baseMonthString' => $dateTime->modify('first day of this month')->format('m'),
                'baseYearString' => $dateTime->modify('first day of this month')->format('Y'),
                'baseMonth_timestamp' => $dateTime->modify('first day of this month')->getTimeStamp()
            )
        );
    }
}
