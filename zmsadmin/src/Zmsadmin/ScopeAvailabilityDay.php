<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Helper\DateTime;
use BO\Zmsentities\Collection\AvailabilityList;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class ScopeAvailabilityDay extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['availability'])) {
            throw new UserAccountMissingRights();
        }
        $data = static::getAvailabilityData(intval($args['id']), $args['date']);
        $data['title'] = 'Behörden und Standorte - Öffnungszeiten';
        $data['menuActive'] = 'owner';
        $data['workstation'] = $workstation;
        $data['canViewAvailabilityHistory'] = ScopeAvailabilityHistory::canViewAvailabilityHistory(
            $workstation->getUseraccount()
        );
        return Render::withHtml(
            $response,
            'page/availabilityday.twig',
            $data
        );
    }

    protected static function getScope(string|int $scopeId)
    {
        return \App::$http->readGetResult('/scope/' . $scopeId . '/', [
            'resolveReferences' => 3
        ])->getEntity();
    }

    /**
     * @param array<string, int> $occupiedByTime
     * @return (int|mixed)[][]
     */
    protected static function getSlotBuckets(AvailabilityList $availabilityList, array $occupiedByTime): array
    {
        $buckets = [];
        foreach ($availabilityList->getSlotListByType('appointment') as $slot) {
            $time = $slot->time;
            if (isset($buckets[$time])) {
                // Overlapping availabilities add up their workstations
                $buckets[$time]['public'] += $slot->public;
                $buckets[$time]['intern'] += $slot->intern;
                continue;
            }
            $buckets[$time] = [
                'time' => $time,
                'timeString' => $slot->getTimeString(),
                'public' => $slot->public,
                'intern' => $slot->intern,
                'occupiedCount' => $occupiedByTime[$time] ?? 0,
            ];
        }
        ksort($buckets);
        return $buckets;
    }

    /**
     * @return (\BO\Zmsentities\Process[]|int|mixed|string)[]
     *
     */
    protected static function getAvailabilityData(int $scopeId, string $dateString): array
    {
        $scope = static::getScope($scopeId);
        $dateTime = new DateTime($dateString);
        $dateWithTime = $dateTime->setTime(\App::$now->format('H'), \App::$now->format('i'));
        $availabilityList = static::readAvailabilityList($scopeId, $dateWithTime);
        $queueList = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/process/' . $dateWithTime->format('Y-m-d') . '/')
                ->getCollection()
                ->toQueueList($dateWithTime)
                ->withoutStatus(['fake']);
        $processList = $queueList->toProcessList();

        $conflictList = static::readConflictList($scopeId, $dateWithTime);
        $maxSlots = $availabilityList->getSummerizedSlotCount();
        $slotUsage = $queueList->withoutStatus(['queued'])->toProcessList()->getAvailabilitySlotUsage($availabilityList);

        return [
            'slotBuckets' => static::getSlotBuckets($availabilityList, $slotUsage['occupiedByTime']),
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
            'busySlotsForAvailabilities' => $slotUsage['busySlots'],
            'today' => \App::$now->getTimestamp()
        ];
    }

    public static function readConflictList(string|int $scopeId, \DateTimeInterface $dateTime)
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

    public static function readAvailabilityList(string|int $scopeId, \DateTimeInterface $dateTime)
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
            if ($exception->template != 'BO\Zmsbackend\Availability\Exception\AvailabilityNotFound') {
                throw $exception;
            }
            $availabilityList = new AvailabilityList();
        }
        return $availabilityList->withDateTime($dateTime); //withDateTime to check if opened
    }
}
