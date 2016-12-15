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
class ScopeAvailabilityDayConflicts extends ScopeAvailabilityDay
{
    /**
     * @return String
     */
    public static function render($scope_id, $dateString)
    {
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
        //$workstation = \App::$http->readGetResult('/workstation/')->getEntity();
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

        $maxSlots = static::getMaxSlotsForAvailabilities($availabilityList);
        $busySlots = static::getBusySlotsForAvailabilities($availabilityList, $processList);

        \BO\Slim\Render::json(array(
            'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
            'conflicts' => $conflicts->getArrayCopy(),
            'maxSlotsForAvailabilities' => $maxSlots,
            'busySlotsForAvailabilities' => $busySlots,
        ));
    }
}
