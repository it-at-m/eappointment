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
    public static function render($scope_id)
    {
        $availabilityList = new AvailabilityList();
        $prefix = \App::APP_PATH . '/tests/examples/Availability'.$scope_id.'/';
        foreach (glob($prefix . 'availability_*.json') as $filename) {
            $availabilityList[] = new Availability(json_decode(file_get_contents($filename), true));
        }
        $scope = json_decode(file_get_contents($prefix . 'scope.json'), true);
        $conflicts = json_decode(file_get_contents($prefix . 'conflicts.json'), true);
        \BO\Slim\Render::withHtml('page/availabilityday.twig', array(
            'availabilityList' => $availabilityList,
            'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
            'conflicts' => $conflicts,
            'scope' => $scope,
            'workstation' => $this->workstation->getArrayCopy(),
            'menuActive' => 'availability',
            'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
        ));
    }
}
