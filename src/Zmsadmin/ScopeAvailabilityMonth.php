<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;

/**
  * Handle requests concerning services
  *
  */
class ScopeAvailabilityMonth extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        /*
        $availabilityList = new AvailabilityList();
        $prefix = \App::APP_PATH . '/tests/examples/Availability'.$scope_id.'/';
        foreach (glob($prefix . 'availability_*.json') as $filename) {
            $availabilityList[] = new Availability(json_decode(file_get_contents($filename), true));
        }
        $scope = json_decode(file_get_contents($prefix . 'scope.json'), true);
        $conflicts = json_decode(file_get_contents($prefix . 'conflicts.json'), true);
        */

        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/')->getEntity();
        $availabilityList = \App::$http->readGetResult('/scope/'. $scopeId .'/availability/')->getCollection();

        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/availabilityMonth.twig',
            array(
                'availabilityList' => $availabilityList,
                'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
                //'conflicts' => $conflicts,
                'scope' => $scope,
                'menuActive' => 'availability',
                'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
            )
        );
    }
}
