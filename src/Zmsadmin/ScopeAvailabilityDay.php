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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scope = \App::$http->readGetResult('/scope/' . intval($scope_id) . '/')->getEntity();
        $data = static::getAvailabilityData($scope_id, $dateString);
        $data['workstation'] = $workstation;
        $data['scope'] = $scope;
        \BO\Slim\Render::html('page/availabilityday.twig', $data);
    }

    protected static function getAvailabilityData($scope_id, $dateString)
    {
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
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
        return [
            'availabilityList' => $availabilityList->getArrayCopy(),
            'availabilityListSlices' => $availabilityList->withCalculatedSlots()->getArrayCopy(),
            'conflicts' => $conflicts->getArrayCopy(),
            'processList' => $processList->getArrayCopy(),
            'dateString' => $dateString,
            'timestamp' => $dateTime->getTimestamp(),
            'menuActive' => 'availability',
            'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
            'maxSlotsForAvailabilities' => $maxSlots,
            'busySlotsForAvailabilities' => $busySlots,
        ];
    }

    /**
     * @return integer
     */
    protected static function getMaxSlotsForAvailabilities($availabilityList)
    {
        return array_reduce($availabilityList->getArrayCopy(), function ($carry, $item) {
            $itemId = $item->id;
            $maxSlots = (int) $item->getSlotList()->getSummerizedSlot()->intern;
            $carry[$itemId] = $maxSlots;
            return $carry;
        }, []);
    }

    /**
     * @return integer
     */
    protected static function getBusySlotsForAvailabilities($availabilityList, $processList)
    {
        return array_reduce($availabilityList->getArrayCopy(), function ($carry, $item) use ($processList) {
            $itemId = $item->id;
            $busySlots = count($processList->withAvailability($item));
            $carry[$itemId] = $busySlots;
            return $carry;
        }, []);
    }
}
