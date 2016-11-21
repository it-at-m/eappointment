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
        /*
        $availabilityList = new AvailabilityList();
        $prefix = \App::APP_PATH . '/tests/examples/Availability'.$scope_id.'/';
        foreach (glob($prefix . 'availability_*.json') as $filename) {
            $availabilityList[] = new Availability(json_decode(file_get_contents($filename), true));
        }
        $scope = json_decode(file_get_contents($prefix . 'scope.json'), true);
        $conflicts = json_decode(file_get_contents($prefix . 'conflicts.json'), true);
         */
        $scope = \App::$http->readGetResult('/scope/' . intval($scope_id) . '/')->getEntity();
        $availabilityList = \App::$http
            ->readGetResult('/scope/' . intval($scope_id) . '/availability/')
            ->getCollection()
            ->withDateTime($dateTime);
        $processList = \App::$http
            ->readGetResult('/scope/' . intval($scope_id) . '/day/' . $dateTime->format('Y-m-d') . '/')
            ->getCollection()
            ;
        $conflicts = [];
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
