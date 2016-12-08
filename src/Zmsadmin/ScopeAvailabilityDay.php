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

        $maxSlots = self::getMaxSlotsForAvailabilities($availabilityList);
        $busySlots = self::getBusySlotsForAvailabilities($availabilityList, $processList);

        \BO\Slim\Render::html('page/availabilityday.twig', array(
            'availabilityList' => $availabilityList->getArrayCopy(),
            'availabilityListSlices' => $availabilityList->withCalculatedSlots(),
            'conflicts' => $conflicts->getArrayCopy(),
            'scope' => $scope,
            'processList' => $processList->getArrayCopy(),
            'dateString' => $dateString,
            'timestamp' => $dateTime->getTimestamp(),
            'workstation' => $workstation,
            'menuActive' => 'availability',
            'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
            'maxSlotsForAvailabilities' => $maxSlots,
            'busySlotsForAvailabilities' => $busySlots,
        ));
    }

    /**
     * @return integer
     */
    protected static function getMaxSlotsForAvailabilities($availabilityList) {
        return array_reduce($availabilityList->getArrayCopy(), function($carry, $item) {
            $id = $item->id;
            $maxSlots = (int) $item->getSlotList()->getSummerizedSlot()->intern;
            $carry[$id] = $maxSlots;
            return $carry;
        }, []);
    }

    /**
     * @return integer
     */
    protected static function getBusySlotsForAvailabilities($availabilityList, $processList) {
        return array_reduce($availabilityList->getArrayCopy(), function($carry, $item) use ($processList) {
            $id = $item->id;
            $busySlots = count($processList->withAvailability($item));
            $carry[$id] = $busySlots;
            return $carry;
        }, []);
    }
}
