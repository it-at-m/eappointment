<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;

/**
  * Handle requests concerning services
  *
  */
class ScopeAvailabilityDay extends BaseController
{
    /**
     * @return String
     */
    public static function render($scope_id, $dateString)
    {
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scope = \App::$http->readGetResult('/scope/' . intval($scope_id) . '/')->getEntity();
        $availabilityList = \App::$http
            ->readGetResult('/scope/' . intval($scope_id) . '/availability/')
            ->getCollection()
            ->withDateTime($dateTime);
        $processList = \App::$http
            ->readGetResult('/scope/' . intval($scope_id) . '/day/' . $dateTime->format('Y-m-d') . '/')
            ->getCollection()
            ;
        $conflicts = $availabilityList->getConflicts();
        if ($processList) {
            $conflicts->addList($processList->withOutAvailability($availabilityList));
        }
        \BO\Slim\Render::html('page/availabilityday.twig', array(
            'availabilityList' => $availabilityList,
            'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
            'conflicts' => $conflicts,
            'scope' => $scope,
            'processList' => $processList,
            'dateString' => $dateString,
            'timestamp' => $dateTime->getTimestamp(),
            'workstation' => $workstation,
            'menuActive' => 'availability',
            'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
        ));
    }
}
