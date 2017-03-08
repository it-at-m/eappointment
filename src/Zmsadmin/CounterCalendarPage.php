<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class CounterCalendarPage extends BaseController
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
        $validator = $request->getAttribute('validator');
        $source = $validator->getParameter('source')->isString()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();

        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scope = new Scope($workstation->scope);
        $calendar = new Helper\PageCalendar($selectedDate);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/calendar/calendarMonth.twig',
            array(
                'title' => 'Kalender',
                'calendar' => $calendar,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'source' => ($source) ? $source : 'counter',
                'dayoffList' => $scope->getDayoffList(),
                'monthList' => $calendar->readByScope($scope)
            )
        );
    }
}
