<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $data = static::getAvailabilityData(intval($args['id']), $args['date']);
        $data['title'] = 'Behörden und Standorte - Öffnungszeiten';
        $data['menuActive'] = 'owner';
        $data['workstation'] = $workstation;
        return \BO\Slim\Render::withHtml(
            $response,
            'page/availabilityday.twig',
            $data
        );
    }

    protected static function getScope($scopeId)
    {
        return \App::$http->readGetResult('/scope/' . $scopeId . '/', [
            'resolveReferences' => 3
        ])->getEntity();
    }

    protected static function getSlotBuckets($availabilityList, $processList) {
        // Initialize buckets from slots
        $buckets = [];
        $slotTimeInMinutes = $availabilityList[0]->getSlotTimeInMinutes();

        foreach ($availabilityList->getSlotListByType('appointment') as $slot) {
            $time = $slot->time; 
            $buckets[$time] = [
                'time' => $time,
                'timeString' => $slot->getTimeString(),
                'public' => $slot->public, 
                'intern' => $slot->intern, 
                'callcenter' => $slot->callcenter,
                'occupiedCount' => 0, 
            ];
        }

        foreach ($processList as $process) {
            $startTime = $process->getAppointments()->getFirst()->getStartTime()->format('H:i');
            $endTime = $process->getAppointments()->getFirst()->getEndTimeWithCustomSlotTime($slotTimeInMinutes)->format('H:i');

            $startDateTime = new \DateTime($startTime);
            $endDateTime = new \DateTime($endTime);
            
            foreach ($buckets as $time => $value) {
                $slotDateTime = new \DateTime($time);
                // Check if the appointment overlaps with the slot time
                if ($slotDateTime >= $startDateTime && $slotDateTime < $endDateTime) {
                    $buckets[$time]['occupiedCount']++;
                }
            }
        }

        return $buckets;
    }

    protected static function getAvailabilityData($scopeId, $dateString)
    {
        $scope = static::getScope($scopeId);
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
        $dateWithTime = $dateTime->setTime(\App::$now->format('H'), \App::$now->format('i'));
        $availabilityList = static::readAvailabilityList($scopeId, $dateWithTime);
        $processList = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/process/' . $dateWithTime->format('Y-m-d') . '/')
                ->getCollection()
                ->toQueueList($dateWithTime)
                ->withoutStatus(['fake'])
                ->toProcessList();
        if (!$processList->count()) {
            $processList = new \BO\Zmsentities\Collection\ProcessList();
        }
        
        
        $conflictList = static::readConflictList($scopeId, $dateWithTime);
        $maxSlots = $availabilityList->getSummerizedSlotCount();
        $busySlots = $availabilityList->getCalculatedSlotCount($processList);

        return [
            'slotBuckets' => static::getSlotBuckets($availabilityList, $processList),
            'scope' => $scope,
            'availabilityList' => $availabilityList->getArrayCopy(),
            'conflicts' => ($conflictList) ? $conflictList
                ->setConflictAmendment()
                ->getArrayCopy() : [],
            'processList' => $processList->getArrayCopy(),
            'dateString' => $dateString,
            'timestamp' => $dateWithTime->getTimestamp(),
            'menuActive' => 'availability',
            'maxWorkstationCount' => $availabilityList->getMaxWorkstationCount(),
            'maxSlotsForAvailabilities' => $maxSlots,
            'busySlotsForAvailabilities' => $busySlots,
            'today' => \App::$now->getTimestamp()
        ];
    }

    public static function readConflictList($scopeId, $dateTime)
    {
        $processConflictList = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/conflict/', [
                'startDate' => $dateTime->format('Y-m-d'),
                'endDate' => $dateTime->format('Y-m-d')
            ])
            ->getCollection();
        return ($processConflictList) ? $processConflictList
            ->sortByAppointmentDate()
            ->withoutDublicatedConflicts()
            ->toQueueList($dateTime)
            ->withoutStatus(['fake', 'queued'])
            ->toProcessList() : null;
    }

    public static function readAvailabilityList($scopeId, $dateTime)
    {
        try {
            $availabilityList = \App::$http
                ->readGetResult(
                    '/scope/' . $scopeId . '/availability/',
                    [
                        'startDate' => $dateTime->format('Y-m-d'), //for skipping old availabilities
                    ]
                )
                ->getCollection()->sortByCustomKey('startDate');
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new \BO\Zmsentities\Collection\AvailabilityList();
        }
        return $availabilityList->withDateTime($dateTime); //withDateTime to check if opened
    }
}
