<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

use BO\Mellon\Validator;

class CalendarWeek extends BaseController
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

        $selectedYear = Validator::value($args['year'])->isNumber()->getValue();
        $selectedWeek = Validator::value($args['weeknr'])->isNumber()->getValue();
        $calendar = new Helper\Calendar(null, $selectedWeek, $selectedYear);
        $clusterHelper = (new Helper\ClusterHelper($workstation));

        return \BO\Slim\Render::withHtml(
            $response,
            'page/calendarWeek.twig',
            array(
                'title' => 'Kalender',
                'workstation' => $workstation,
                'source' => $workstation->getVariantName(),
                'cluster' => $clusterHelper->getEntity(),
                'calendar' => $calendar,
                'selectedYear' => $selectedYear,
                'selectedWeek' => $selectedWeek,
                'selectedDate' => $calendar->getDateTime()->format('Y-m-d'),
                'dayList' => $calendar->readWeekDayListWithProcessList($clusterHelper->getScopeList())->toSortedByHour()
            )
        );
    }
}
