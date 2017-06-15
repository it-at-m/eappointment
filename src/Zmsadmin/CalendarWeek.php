<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
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

        $scopeList = (new Helper\ClusterHelper($workstation))->getScopeList();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/calendarWeek.twig',
            array(
                'title' => 'Kalender',
                'workstation' => $workstation,
                'source' => $workstation->getRedirect(),
                'cluster' => ($cluster) ? $cluster : null,
                'calendar' => $calendar,
                'selectedYear' => $selectedYear,
                'selectedWeek' => $selectedWeek,
                'selectedDate' => $calendar->getDateTime()->format('Y-m-d'),
                'dayList' => $calendar->readWeekDayListWithProcessList($scopeList)->toSortedByHour()
            )
        );
    }
}
