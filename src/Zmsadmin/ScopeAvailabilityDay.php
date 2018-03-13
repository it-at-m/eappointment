<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;

class ScopeAvailabilityDay extends BaseController
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
        $scope = \App::$http->readGetResult('/scope/' . intval($args['id']) . '/')->getEntity();
        $data = static::getAvailabilityData($scope->id, $args['date']);
        $data['workstation'] = $workstation;
        $data['scope'] = $scope;
        $data['title'] = 'Behörden und Standorte - Öffnungszeiten';
        $data['menuActive'] = 'owner';
        return \BO\Slim\Render::withHtml(
            $response,
            'page/availabilityday.twig',
            $data
        );
    }

    protected static function getAvailabilityData($scope_id, $dateString)
    {
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
        try {
            $availabilityList = \App::$http
                ->readGetResult('/scope/' . intval($scope_id) . '/availability/', [
                    'reserveEntityIds' => 1,
                    'resolveReferences' => 2
                ])
                ->getCollection()
                ->withDateTime($dateTime);
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        }
        $processList = \App::$http
            ->readGetResult('/scope/' . intval($scope_id) . '/process/' . $dateTime->format('Y-m-d') . '/')
            ->getCollection();
        $processList = ($processList) ? $processList : new \BO\Zmsentities\Collection\ProcessList();
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
            $busySlots = $processList->withAvailability($item)->getAppointmentList()->getCalculatedSlotCount();
            $carry[$itemId] = $busySlots;
            return $carry;
        }, []);
    }
}
