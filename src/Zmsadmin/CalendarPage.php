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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scope = new Scope($workstation->scope);
        $calendar = new Helper\PageCalendar($args);
        $validator = $request->getAttribute('validator');
        $source = $validator->getParameter('source')->isString()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/calendarPage.twig',
            array(
                'calendar' => $calendar,
                'selecteddate' => ($selectedDate) ? $selectedDate : \Appp::$now->format('Y-m-d'),
                'source' => ($source) ? $source : 'counter',
                'dayoffList' => $scope->getDayoffList(),
                'monthList' => $calendar->readByScope($scope)
            )
        );
    }
}
