<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class ScopeAvailabilityDay extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $availabilityList = new AvailabilityList();
        $prefix = \App::APP_PATH . '/tests/examples/Availability01/';
        $availabilityList[] = json_decode(file_get_contents($prefix . 'availability_79813.json'), true);
        $availabilityList[] = json_decode(file_get_contents($prefix . 'availability_98495.json'), true);
        $availabilityList[] = json_decode(file_get_contents($prefix . 'availability_98501.json'), true);
        $availabilityList[] = json_decode(file_get_contents($prefix . 'availability_98507.json'), true);
        $availabilityList[] = json_decode(file_get_contents($prefix . 'availability_o.json'), true);
        $scope = json_decode(file_get_contents($prefix . 'scope.json'), true);
        \BO\Slim\Render::html('page/availabilityday.twig', array(
            'availabilityList' => $availabilityList,
            'scope' => $scope,
            'menuActive' => 'availability',
            'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
        ));
    }
}

