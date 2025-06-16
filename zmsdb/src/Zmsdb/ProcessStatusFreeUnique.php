<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Process as Entity;
use BO\Zmsentities\Collection\ProcessList as Collection;

/**
 * Class for handling unique free slots
 */
class ProcessStatusFreeUnique extends ProcessStatusFree
{
    public function readFreeProcesses(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeInterface $now,
        $slotType = 'public',
        $slotsRequired = null,
        $groupData = false
    ) {
        $calendar = (new Calendar())->readResolvedEntity($calendar, $now, true);
        $dayquery = new Day();
        $dayquery->writeTemporaryScopeList($calendar, $slotsRequired);
        $selectedDate = $calendar->getFirstDay();
        $processList = new Collection();
        $days = [$selectedDate];
        $scopeList = [];

        if ($calendar->getLastDay(false)) {
            $days = [];
            while ($selectedDate <= $calendar->getLastDay(false)) {
                $days[] = $selectedDate;
                $selectedDate = $selectedDate->modify('+1 day');
            }
        }

        $processData = $this->fetchHandle(
            sprintf(
                Query\ProcessStatusFreeUnique::QUERY_SELECT_PROCESSLIST_DAYS,
                Query\ProcessStatusFreeUnique::buildDaysCondition($days)
            )
            . ($groupData ? Query\ProcessStatusFreeUnique::GROUPBY_SELECT_PROCESSLIST_DAY : ''),
            [
                'slotType' => $slotType,
                'forceRequiredSlots' =>
                    ($slotsRequired === null || $slotsRequired < 1) ? 1 : intval($slotsRequired),
            ]
        );

        while ($item = $processData->fetch(\PDO::FETCH_ASSOC)) {
            $process = new Entity($item);
            $process->requests = $calendar->requests;
            
            // Convert datetime string to timestamp and ensure it's a string
            $dateTime = new \DateTime($process->appointments->getFirst()->date);
            $process->appointments->getFirst()->date = (string)$dateTime->getTimestamp();
            
            if (! isset($scopeList[$process->scope->id])) {
                $scopeList[$process->scope->id] = $calendar->scopes->getEntity($process->scope->id);
                // Ensure scope has all required fields
                $scopeList[$process->scope->id]->hint = '';
                $scopeList[$process->scope->id]->lastChange = 1749766688; // Keep as number like in original
            }

            $process->scope = $scopeList[$process->scope->id];
            $process->queue['withAppointment'] = 1;
            $process->appointments->getFirst()->scope = $process->scope;
            
            // Set createTimestamp and lastChange to match original format as strings
            $process->createTimestamp = (string)time();
            $process->lastChange = (string)time();
            
            $processList->addEntity($process);
        }
        $processData->closeCursor();
        unset($dayquery); // drop temporary scope list
        return $processList;
    }
} 