<?php

namespace BO\Zmsdb;

use BO\Dldb\Helper\DateTime;
use BO\Zmsentities\Slot as Entity;
use BO\Zmsentities\Collection\SlotList as Collection;
use BO\Zmsentities\Availability as AvailabilityEntity;
use BO\Zmsentities\Scope as ScopeEntity;

/**
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Coupling)
 */
class Slot extends Base
{
    /**
     * maximum number of slots per appointment
     */
    const MAX_SLOTS = 25;

    const MAX_DAYS_OF_SLOT_CALCULATION = 10;

    /**
     * @return \BO\Zmsentities\Collection\SlotList
     *
     */
    public function readByAppointment(
        \BO\Zmsentities\Appointment $appointment,
        $overwriteSlotsCount = null,
        $extendSlotList = false
    ) {
        $appointment = clone $appointment;
        $availability = (new Availability())->readByAppointment($appointment);
        // Check if availability allows multiple slots, but allow overwrite
        if (!$availability->multipleSlotsAllowed || $overwriteSlotsCount >= 1) {
            $appointment->slotCount = ($overwriteSlotsCount >= 1) ? $overwriteSlotsCount : 1;
        }
        $slotList = $availability->getSlotList()->withSlotsForAppointment($appointment, $extendSlotList);
        foreach ($slotList as $slot) {
            $this->readByAvailability($slot, $availability, $appointment->toDateTime());
        }
        return $slotList;
    }

    public function readByAvailability(
        \BO\Zmsentities\Slot $slot,
        AvailabilityEntity $availability,
        \DateTimeInterface $date,
        $getLock = false
    ) {
        $data = array();
        $data['scopeID'] = $availability->scope->id;
        $data['availabilityID'] = $availability->id;
        $data['year'] = $date->format('Y');
        $data['month'] = $date->format('m');
        $data['day'] = $date->format('d');
        $data['time'] = $slot->getTimeString();
        $sql = Query\Slot::QUERY_SELECT_SLOT;
        if ($getLock) {
            $sql .= ' FOR UPDATE';
        }
        $slotID = $this->fetchRow(
            $sql,
            $data
        );
        return $slotID ? $slotID['slotID'] : false ;
    }

    public function hasScopeRelevantChanges(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $slotLastChange = null
    ) {
        $startInDaysDefault = (new Preferences())
            ->readProperty('scope', $scope->id, 'appointment', 'startInDaysDefault');
        $endInDaysDefault = (new Preferences())
            ->readProperty('scope', $scope->id, 'appointment', 'endInDaysDefault');
        if (
            $scope->preferences['appointment']['startInDaysDefault'] != $startInDaysDefault
            || $scope->preferences['appointment']['endInDaysDefault'] != $endInDaysDefault
        ) {
            (new Scope())->replacePreferences($scope); //TODO remove after ZMS1 is deactivated
            return true;
        }
        $startInDaysChange = (new Preferences())
            ->readChangeDateTime('scope', $scope->id, 'appointment', 'startInDaysDefault');
        $endInDaysChange = (new Preferences())
            ->readChangeDateTime('scope', $scope->id, 'appointment', 'endInDaysDefault');
        if (
            $startInDaysChange->getTimestamp() > $slotLastChange->getTimestamp()
            || $endInDaysChange->getTimestamp() > $slotLastChange->getTimestamp()
        ) {
            return true;
        }
    }

    public function isAvailabilityOutdated(
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $now,
        \DateTimeInterface $slotLastChange = null
    ) {
        $proposedChange = new Helper\AvailabilitySnapShot($availability, $now);
        $formerChange = new Helper\AvailabilitySnapShot($availability, $slotLastChange);

        if ($formerChange->hasOutdatedAvailability()) {
            $availability['processingNote'][] = 'outdated: availability change';
            return true;
        }
        if (
            $formerChange->hasOutdatedScope()
            && $this->hasScopeRelevantChanges($availability->scope, $slotLastChange)
        ) {
            $availability['processingNote'][] = 'outdated: scope change';
            return true;
        }
        if ($formerChange->hasOutdatedDayoff()) {
            $availability['processingNote'][] = 'outdated: dayoff change';
            return true;
        }
        // Be aware, that last slot change and current time might differ serval days
        //  if the rebuild fails in some way
        if (
            1
            // First check if the bookable end date on current time was already calculated on last slot change
            && !$formerChange->hasBookableDateTime($proposedChange->getLastBookableDateTime())
            // Second check if between last slot change and current time could be a bookable slot
            && (
                (
                    !$formerChange->isOpenedOnLastBookableDay()
                    && $proposedChange->hasBookableDateTimeAfter($formerChange->getLastBookableDateTime())
                )
                // if calculation already happened the day before, check if lastChange time was before opening
                || (
                    $formerChange->isOpenedOnLastBookableDay()
                    && (
                        !$formerChange->isTimeOpenedOnLastBookableDay()
                        || $proposedChange->hasBookableDateTimeAfter(
                            $formerChange->getLastBookableDateTime()->modify('+1day 00:00:00')
                        )
                    )
                )
            )
            // Check if daytime is after booking start time if bookable end of now is calculated
            && (
                !$proposedChange->isOpenedOnLastBookableDay()
                || $proposedChange->isTimeOpenedOnLastBookableDay()
            )
        ) {
            $availability['processingNote'][] = 'outdated: new slots required';
            return true;
        }
        if (
            $availability->getBookableStart($slotLastChange) != $availability->getBookableStart($now)
            // First check, if bookable start from lastChange was not included in bookable time from now
            && !$availability->hasDate($availability->getBookableStart($slotLastChange), $now)
            // Second check, if availability had a bookable time on lastChange before bookable start from now
            && $availability->hasDateBetween(
                $availability->getBookableStart($slotLastChange),
                $availability->getBookableStart($now),
                $slotLastChange
            )
        ) {
            $availability['processingNote'][] = 'outdated: slots invalidated by bookable start';
            return true;
        }
        $availability['processingNote'][] = 'not outdated';
        return false;
    }

    /**
     * @return bool TRUE if there were changes on slots
     */
    public function writeByAvailability(
        \BO\Zmsentities\Availability $availability,
        \DateTimeInterface $now,
        \DateTimeInterface $slotLastChange = null
    ) {
        $now = \BO\Zmsentities\Helper\DateTime::create($now);
        $calculateSlotsUntilDate = \BO\Zmsentities\Helper\DateTime::create($now)->modify('+' . self::MAX_DAYS_OF_SLOT_CALCULATION . ' days');
        if (!$slotLastChange) {
            $slotLastChange = $this->readLastChangedTimeByAvailability($availability);
        }
        $lastGeneratedSlotDate = $this->getLastGeneratedSlotDate($availability);

        $availability['processingNote'][] = 'lastchange=' . $slotLastChange->format('c');
        if (!$this->isAvailabilityOutdated($availability, $now, $slotLastChange)) {
            return false;
        }

        $generateNew = $availability->isNewerThan($slotLastChange);

        (new Availability())->readLock($availability->id);
        if ($generateNew) {
            $cancelledSlots = $this->fetchAffected(Query\Slot::QUERY_CANCEL_AVAILABILITY, [
                'availabilityID' => $availability->id,
            ]);
            if (!$availability->withData(['bookable' => ['startInDays' => 0]])->hasBookableDates($now)) {
                $availability['processingNote'][] = "cancelled $cancelledSlots slots: availability not bookable ";
                return ($cancelledSlots > 0) ? true : false;
            }
            $availability['processingNote'][] = "cancelled $cancelledSlots slots";
        }

        $startDate = $availability->getBookableStart($now)->modify('00:00:00');
        $stopDate = $availability->getBookableEnd($now);
        $slotlist = $availability->getSlotList();
        $slotlistIntern = $slotlist->withValueFor('callcenter', 0)->withValueFor('public', 0);
        $time = $now->modify('00:00:00');
        if (!$generateNew) {
            $time = $lastGeneratedSlotDate->modify('+1 day')->modify('00:00:00');
        }
        $status = false;
        do {
            if ($availability->withData(['bookable' => ['startInDays' => 0]])->hasDate($time, $now)) {
                $writeStatus = $this->writeSlotListForDate(
                    $time,
                    ($time->getTimestamp() < $startDate->getTimestamp()) ? $slotlistIntern : $slotlist,
                    $availability
                );
                $status = $writeStatus ? $writeStatus : $status;
            }
            $time = $time->modify('+1day');
        } while ($time->getTimestamp() <= $stopDate->getTimestamp() && $time->getTimestamp() < $calculateSlotsUntilDate->getTimestamp());

        return $status || (isset($cancelledSlots) && $cancelledSlots > 0);
    }

    public function writeByScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now)
    {
        $slotLastChange = $this->readLastChangedTimeByScope($scope);
        $availabilityList = (new \BO\Zmsdb\Availability())
            ->readAvailabilityListByScope($scope, 0, $slotLastChange->modify('-1 day'))
            ;
        $updatedList = new \BO\Zmsentities\Collection\AvailabilityList();
        foreach ($availabilityList as $availability) {
            $availability->scope = clone $scope; //dayoff is required
            if ($this->writeByAvailability($availability, $now)) {
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
        $hasAddedSlots = false;
        foreach ($slotlist as $slot) {
            $slot = clone $slot;
            $slotID = $this->readByAvailability($slot, $availability, $time);
            if ($slotID) {
                $query = new Query\Slot(Query\Base::UPDATE);
                $query->addConditionSlotId($slotID);
            } else {
                $query = new Query\Slot(Query\Base::INSERT);
                $hasAddedSlots = true;
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
            // TODO: Check if slot changed before writing ancestor IDs
            $this->writeAncestorIDs($slotID, $ancestors);
            $status = $writeStatus ? $writeStatus : $status;
        }
        if ($hasAddedSlots) {
            $availability['processingNote'][] = 'Added ' . $time->format('Y-m-d');
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
        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsdb\Connection\Select::$connectionTimezone);
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
        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsdb\Connection\Select::$connectionTimezone);
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
        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsdb\Connection\Select::$connectionTimezone);
    }

    public function updateSlotProcessMapping($scopeID = null)
    {
        if ($scopeID) {
            $processIdList = $this->fetchAll(
                Query\Slot::QUERY_SELECT_MISSING_PROCESS
                . Query\Slot::QUERY_SELECT_MISSING_PROCESS_BY_SCOPE,
                ['scopeID' => $scopeID]
            );
        } else {
            $processIdList = $this->fetchAll(Query\Slot::QUERY_SELECT_MISSING_PROCESS, []);
        }
        // Client side INSERT ... SELECT ... to reduce table locking
        foreach ($processIdList as $processId) {
            $this->perform(Query\Slot::QUERY_INSERT_SLOT_PROCESS, array_values($processId));
        }
        return count($processIdList);
    }

    public function deleteSlotProcessOnSlot($scopeID = null)
    {
        if ($scopeID) {
            $this->perform(
                Query\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED
                . Query\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED_BY_SCOPE,
                ['scopeID' => $scopeID]
            );
        } else {
            $this->perform(Query\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED, []);
        }
    }

    public function deleteSlotProcessOnProcess($scopeID = null)
    {
        if ($scopeID) {
            $processIdList = $this->fetchAll(
                Query\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS
                . Query\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS_BY_SCOPE,
                ['scopeID' => $scopeID]
            );
        } else {
            $processIdList = $this->fetchAll(Query\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS);
        }
        // Client side INSERT ... SELECT ... to reduce table locking
        foreach ($processIdList as $processId) {
            $this->perform(Query\Slot::QUERY_DELETE_SLOT_PROCESS_ID, $processId);
        }
        return count($processIdList);
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

    public function writeCanceledByTimeAndScope(\DateTimeInterface $dateTime, \BO\Zmsentities\Scope $scope)
    {
        $status = $this->perform(Query\Slot::QUERY_UPDATE_SLOT_MISSING_AVAILABILITY_BY_SCOPE, [
            'dateString' => $dateTime->format('Y-m-d'),
            'scopeID' => $scope->id,
        ]);
        return $this->perform(Query\Slot::QUERY_CANCEL_SLOT_OLD_BY_SCOPE, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
            'time' => $dateTime->format('H:i:s'),
            'scopeID' => $scope->id,
        ]) && $status;
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
        return $status;
    }

    /**
     * This function is for debugging
     */
    public function readRowsByScopeAndDate(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $dateTime
    ) {
        $list = $this->fetchAll(Query\Slot::QUERY_SELECT_BY_SCOPE_AND_DAY, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
            'scopeID' => $scope->id,
        ]);
        return $list;
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

    private function getLastGeneratedSlotDate(AvailabilityEntity $availability)
    {
        $date = '1970-01-01 12:00';
        $last = $this->fetchRow(
            Query\Slot::QUERY_LAST_IN_AVAILABILITY,
            [
                'availabilityID' => $availability->id,
            ]
        );

        if (isset($last['dateString'])) {
            $date = $last['dateString'];
        }

        return new \DateTimeImmutable($date . \BO\Zmsdb\Connection\Select::$connectionTimezone);
    }
}
