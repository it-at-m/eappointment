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
use BO\Zmsentities\Collection\ProcessList;

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
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        
        $dateTime = (isset($args['date'])) ? new \BO\Zmsentities\Helper\DateTime($args['date']) : \App::$now;
        $startDate = $dateTime->modify('first day of this month');
        $endDate = $dateTime->modify('last day of this month');

        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/', [
            'resolveReferences' => 1
        ])->getEntity();
        $calendar = new Helper\Calendar($dateTime->format('Y-m-d'));
        $scopeList = (new \BO\Zmsentities\Collection\ScopeList)->addEntity($scope);
        $month = $calendar->readMonthListByScopeList($scopeList, 'intern', 0)->getFirst();

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
                'month' => $month,
                'scope' => $scope,
                'menuActive' => 'owner',
                'title' => 'Behörden und Standorte - Öffnungszeiten',
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
}
