<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class CalendarPage extends BaseController
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
        $cluster = null;
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();

        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scope = new Scope($workstation->scope);
        $calendar = new Helper\Calendar($selectedDate);

        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        }
        $scopeList = $workstation->getScopeList($cluster);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/calendar/calendarMonth.twig',
            array(
                'title' => 'Kalender',
                'calendar' => $calendar,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'dayoffList' => $scope->getDayoffList(),
                'monthList' => $calendar->readMonthListByScopeList($scopeList)
            )
        );
    }
}
