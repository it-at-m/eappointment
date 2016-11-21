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
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        /*
        $availabilityList = new AvailabilityList();
        $prefix = \App::APP_PATH . '/tests/examples/Availability'.$scope_id.'/';
        foreach (glob($prefix . 'availability_*.json') as $filename) {
            $availabilityList[] = new Availability(json_decode(file_get_contents($filename), true));
        }
        $scope = json_decode(file_get_contents($prefix . 'scope.json'), true);
        $conflicts = json_decode(file_get_contents($prefix . 'conflicts.json'), true);
        */
        $dateTime = \App::$now;
        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/')->getEntity();
        $availabilityList = \App::$http->readGetResult('/scope/'. $scopeId .'/availability/')->getCollection();
        $calendar = new Calendar();
        $calendar->firstDay->setDateTime($dateTime->modify('first day of this month'));
        $calendar->lastDay->setDateTime($dateTime->modify('last day of this month'));
        $calendar->scopes[] = $scope;
        $calendar = \App::$http->readPostResult('/calendar/', $calendar)->getEntity();
        $month = (new Month(["year" => $dateTime->format('Y'), "month" => $dateTime->format('m')]))
            ->getWithStatedDayList($dateTime)
            ->setDays($calendar->getDayList());

        return \BO\Slim\Render::withHtml(
            $response,
            'page/availabilityMonth.twig',
            array(
                'availabilityList' => $availabilityList,
                'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
                //'conflicts' => $conflicts,
                'calendar' => $calendar,
                'month' => $month,
                'scope' => $scope,
                'menuActive' => 'availability',
                'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
                'today' => \App::$now->format('Y-m-d'),
                'baseMonthString' => \App::$now->format('m'),
                'baseYearString' => \App::$now->format('Y'),
                'baseMonth_timestamp' => \App::$now->getTimeStamp()
            )
        );
    }
}
