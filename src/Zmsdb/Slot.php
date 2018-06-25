<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Slot as Entity;
use \BO\Zmsentities\Collection\SlotList as Collection;
use \BO\Zmsentities\Availability as AvailabilityEntity;
use \BO\Zmsentities\Scope as ScopeEntity;

/**
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Complexity)
 */
class Slot extends Base
{

    /**
     * maximum number of slots per appointment
     */
    const MAX_SLOTS = 10;

    /**
     * @return \BO\Zmsentities\Collection\SlotList
     *
     */
    public function readByAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $availability = (new Availability())->readByAppointment($appointment);
        $slotList = $availability->getSlotList()->withSlotsForAppointment($appointment);
        return $slotList;
    }

    public function readByAvailability(
        \BO\Zmsentities\Slot $slot,
        AvailabilityEntity $availability,
        \DateTimeInterface $date
    ) {
        $data = array();
        $data['scopeID'] = $availability->scope->id;
        $data['availabilityID'] = $availability->id;
        $data['year'] = $date->format('Y');
        $data['month'] = $date->format('m');
        $data['day'] = $date->format('d');
        $data['time'] = $slot->getTimeString();
        $slotID = $this->fetchRow(
            Query\Slot::QUERY_SELECT_SLOT,
            $data
        );
        return $slotID ? $slotID['slotID'] : false ;
    }

    public function isAvailabilityOutdated(
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $now,
        \DateTimeInterface $slotLastChange = null
    ) {
        if ($availability->isNewerThan($slotLastChange)) {
            $availability['processingNote'][] = 'outdated: availability change';
            return true;
        }
        if ($availability->scope->isNewerThan($slotLastChange)) {
            $availability['processingNote'][] = 'outdated: scope change';
            return true;
        }
        if ($availability->scope->dayoff->isNewerThan($slotLastChange, $availability, $now)) {
            $availability['processingNote'][] = 'outdated: dayoff change';
            return true;
        }
        // First check if the bookable end date on current time is already calculated on last slot change
        // Second check if between last slot change and current time could be a bookable slot
        // Be aware, that last slot change and current time might differ serval days if the rebuild fails in some way
        if (!$availability->hasDate($availability->getBookableEnd($now), $slotLastChange)
            && $availability->hasDateBetween(
                $availability->getBookableEnd($slotLastChange),
                $availability->getBookableEnd($now),
                $now
            )
        ) {
            $availability['processingNote'][] = 'outdated: new slots required';
            return true;
        }
        //error_log("Not outdated: $availability");
        return false;
    }

    public function writeByAvailability(
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $now,
        \DateTimeInterface $slotLastChange = null
    ) {
        $now = \BO\Zmsentities\Helper\DateTime::create($now);
        if (!$slotLastChange) {
            $slotLastChange = $this->readLastChangedTimeByAvailability($availability);
        }
        if (!$this->isAvailabilityOutdated($availability, $now, $slotLastChange)) {
            return false;
        }
        // Order is import, the following cancels all slots
        // and should only happen, if rebuild is triggered
        $this->perform(Query\Slot::QUERY_CANCEL_AVAILABILITY, [
            'availabilityID' => $availability->id,
        ]);
        if (!$availability->hasBookableDates($now)) {
            $availability['processingNote'][] = 'cancelled: not bookable';
            return false;
        }
        $stopDate = $availability->getBookableEnd($now);
        $slotlist = $availability->getSlotList();
        $time = $now;
        $status = false;
        do {
            if ($availability->hasDate($time, $now)) {
                $writeStatus = $this->writeSlotListForDate($time, $slotlist, $availability);
                $status = $writeStatus ? $writeStatus : $status;
            }
            $time = $time->modify('+1day');
        } while ($time->getTimestamp() <= $stopDate->getTimestamp());

        return $status;
    }

    public function writeByScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now)
    {
        $slotLastChange = $this->readLastChangedTimeByScope($scope);
        $availabilityList = (new \BO\Zmsdb\Availability)
            ->readAppointmentListByScope($scope, 0, $now->modify('-1 day'))
            ;
        $updatedList = new \BO\Zmsentities\Collection\AvailabilityList();
        foreach ($availabilityList as $availability) {
            $availability->scope = clone $scope; //dayoff is required
            if ($this->writeByAvailability($availability, $now, $slotLastChange)) {
                $updatedList->addEntity($availability);
            }
        }
        return $updatedList;
    }

    protected function writeSlotListForDate(
        \DateTimeInterface $time,
        Collection $slotlist,
        AvailabilityEntity $availability
    ) {
        $ancestors = [];
        foreach ($slotlist as $slot) {
            $slot = clone $slot;
            $slotID = $this->readByAvailability($slot, $availability, $time);
            if ($slotID) {
                $query = new Query\Slot(Query\Base::UPDATE);
                $query->addConditionSlotId($slotID);
            } else {
                $query = new Query\Slot(Query\Base::INSERT);
            }
            $slot->status = 'free';
            $values = $query->reverseEntityMapping($slot, $availability, $time);
            $values['createTimestamp'] = time();
            $query->addValues($values);
            $writeStatus = $this->writeItem($query);
            if ($writeStatus && !$slotID) {
                $slotID = $this->getWriter()->lastInsertId();
            }
            $ancestors[] = $slotID;
            $this->writeAncestorIDs($slotID, $ancestors);
            $status = $writeStatus ? $writeStatus : $status;
        }
        return $status;
    }

    protected function writeAncestorIDs($slotID, array $ancestors)
    {
        $this->perform(Query\Slot::QUERY_DELETE_ANCESTOR, [
            'slotID' => $slotID,
        ]);
        $ancestorLevel = count($ancestors);
        foreach ($ancestors as $ancestorID) {
            if ($ancestorLevel <= self::MAX_SLOTS) {
                $this->perform(Query\Slot::QUERY_INSERT_ANCESTOR, [
                    'slotID' => $slotID,
                    'ancestorID' => $ancestorID,
                    'ancestorLevel' => $ancestorLevel,
                ]);
            }
            $ancestorLevel--;
        }
    }

    public function readLastChangedTime()
    {
        $last = $this->fetchRow(
            Query\Slot::QUERY_LAST_CHANGED
        );
        if (!$last['dateString']) {
            $last['dateString'] = '1970-01-01 12:00';
        }
        return new \DateTimeImmutable($last['dateString']);
    }

    public function readLastChangedTimeByScope(ScopeEntity $scope)
    {
        $last = $this->fetchRow(
            Query\Slot::QUERY_LAST_CHANGED_SCOPE,
            [
                'scopeID' => $scope->id,
            ]
        );
        if (!$last['dateString']) {
            $last['dateString'] = '1970-01-01 12:00';
        }
        return new \DateTimeImmutable($last['dateString']);
    }

    public function readLastChangedTimeByAvailability(AvailabilityEntity $availabiliy)
    {
        $last = $this->fetchRow(
            Query\Slot::QUERY_LAST_CHANGED_AVAILABILITY,
            [
                'availabilityID' => $availabiliy->id,
            ]
        );
        if (!$last['dateString']) {
            $last['dateString'] = '1970-01-01 12:00';
        }
        return new \DateTimeImmutable($last['dateString']);
    }

    public function updateSlotProcessMapping()
    {
        $processIdList = $this->fetchAll(Query\Slot::QUERY_SELECT_MISSING_PROCESS, [
        ]);
        // Client side INSERT ... SELECT ... to reduce table locking
        foreach ($processIdList as $processId) {
            $this->perform(Query\Slot::QUERY_INSERT_SLOT_PROCESS, array_values($processId));
        }
        return count($processIdList);
    }

    public function deleteSlotProcessOnSlot()
    {
        $this->perform(Query\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED, [
        ]);
    }

    public function deleteSlotProcessOnProcess()
    {
        $this->perform(Query\Slot::QUERY_DELETE_SLOT_PROCESS, [
        ]);
        $processIdList = $this->fetchAll(Query\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS);
        // Client side INSERT ... SELECT ... to reduce table locking
        foreach ($processIdList as $processId) {
            $this->perform(Query\Slot::QUERY_DELETE_SLOT_PROCESS_ID, $processId);
        }
        return $processIdList;
    }

    public function writeSlotProcessMappingFor($processId)
    {
        $this->perform(Query\Slot::QUERY_INSERT_SLOT_PROCESS_ID, [
            'processId' => $processId,
        ]);
        return $this;
    }

    public function deleteSlotProcessMappingFor($processId)
    {
        $this->perform(Query\Slot::QUERY_DELETE_SLOT_PROCESS_ID, [
            'processId' => $processId,
        ]);
        return $this;
    }

    public function writeCanceledByTime(\DateTimeInterface $dateTime)
    {
        $status = $this->perform(Query\Slot::QUERY_UPDATE_SLOT_MISSING_AVAILABILITY, [
            'dateString' => $dateTime->format('Y-m-d'),
        ]);
        return $this->perform(Query\Slot::QUERY_CANCEL_SLOT_OLD, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
            'time' => $dateTime->format('H:i:s'),
        ]) && $status;
    }

    public function deleteSlotsOlderThan(\DateTimeInterface $dateTime)
    {
        $status = $this->perform(Query\Slot::QUERY_DELETE_SLOT_OLD, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
        ]);
        $status = ($status && $this->perform(Query\Slot::QUERY_DELETE_SLOT_HIERA));
        $status = ($status && $this->writeOptimizedSlotTables());
        return $status;
    }

    public function writeOptimizedSlotTables()
    {
        $status = true;
        $status = ($status && $this->perform(Query\Slot::QUERY_OPTIMIZE_SLOT));
        $status = ($status && $this->perform(Query\Slot::QUERY_OPTIMIZE_SLOT_HIERA));
        $status = ($status && $this->perform(Query\Slot::QUERY_OPTIMIZE_SLOT_PROCESS));
        $status = ($status && $this->perform(Query\Slot::QUERY_OPTIMIZE_PROCESS));
        return $status;
    }
}
