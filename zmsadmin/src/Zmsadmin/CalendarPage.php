<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Scope;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();

        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $slotType = $validator->getParameter('slotType')->isString()->getValue();
        $slotsRequired = $validator->getParameter('slotsRequired')->isNumber()->getValue();
        $selectedScopeId = $validator->getParameter('selectedscope')->isNumber()->getValue();

        $scope = Helper\AppointmentFormHelper::readSelectedScope($request, $workstation);
        $scope = ($scope) ? $scope : new \BO\Zmsentities\Scope($workstation->scope);
        $calendar = new Helper\Calendar($selectedDate);

        $scopeList = ($selectedScopeId)
            ? (new \BO\Zmsentities\Collection\ScopeList())->addEntity($scope)
            : (new Helper\ClusterHelper($workstation))->getScopeList();

        $slotsRequired = ($scope && $scope->getPreference('appointment', 'multipleSlotsEnabled')) ? $slotsRequired : 0;
        return \BO\Slim\Render::withHtml(
            $response,
            'block/calendar/calendarMonth.twig',
            array(
                'title' => 'Kalender',
                'calendar' => $calendar,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedYear' => $calendar->getDateTime()->format('Y'),
                'selectedWeek' => $calendar->getDateTime()->format('W'),
                'dayoffList' => $scope->getDayoffList(),
                'scopeList' => $scopeList,
                'monthList' => $calendar->readMonthListByScopeList($scopeList, $slotType, $slotsRequired)
            )
        );
    }
}
