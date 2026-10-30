<?php

namespace BO\Zmsbackend\Slot\Service;

use BO\Zmsdldb\Helper\DateTime;
use BO\Zmsentities\Slot as Entity;
use BO\Zmsentities\Collection\SlotList as Collection;
use BO\Zmsentities\Availability as AvailabilityEntity;
use BO\Zmsentities\Scope as ScopeEntity;

/**
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Coupling)
 */
class Slot extends \BO\Zmsbackend\Base
{
    /**
     * maximum number of slots per appointment
     */
    const int MAX_SLOTS = 48;

    const int MAX_DAYS_OF_SLOT_CALCULATION = 180;

    /**
     * Columns written for one slot row, apart from slotID.
     * @var list<string>
     */
    private const array SLOT_WRITE_COLUMNS = [
        'scopeID',
        'availabilityID',
        'version',
        'year',
        'month',
        'day',
        'time',
        'public',
        'intern',
        'status',
        'slotTimeInMinutes',
        'createTimestamp',
    ];

    /**
     * @return \BO\Zmsentities\Collection\SlotList
     *
     */
    public function readByAppointment(
        \BO\Zmsentities\Appointment $appointment,
        int|null $overwriteSlotsCount = null,
        bool $extendSlotList = false,
        bool $lockSlots = false
    ) {
        $appointment = clone $appointment;
        $availability = (new \BO\Zmsbackend\Availability\Service\Availability())->readByAppointment($appointment);
        // Check if availability allows multiple slots, but allow overwrite
        if (!$availability->multipleSlotsAllowed || $overwriteSlotsCount >= 1) {
            $appointment->slotCount = ($overwriteSlotsCount >= 1) ? $overwriteSlotsCount : 1;
        }
        $slotList = $availability->getSlotList()->withSlotsForAppointment($appointment, $extendSlotList);
        if ($lockSlots && count($slotList) > 0) {
            // One FOR UPDATE for all slots avoids row-by-row lock interleaving (deadlocks with calculateSlots).
            $this->lockSlotsForAppointment($slotList, $availability, $appointment->toDateTime());
        } else {
            foreach ($slotList as $slot) {
                $this->readByAvailability($slot, $availability, $appointment->toDateTime(), false);
            }
        }
        return $slotList;
    }

    public function readByAvailability(
        \BO\Zmsentities\Slot $slot,
        AvailabilityEntity $availability,
        \DateTimeInterface $date,
        bool $getLock = false
    ) {
        $data = array();
        $data['scopeID'] = $availability['scope']['id'];
        $data['availabilityID'] = $availability['id'];
        $data['year'] = $date->format('Y');
        $data['month'] = $date->format('m');
        $data['day'] = $date->format('d');
        $data['time'] = $slot->getTimeString();
        $sql = \BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_SLOT;
        if ($getLock) {
            $sql .= ' FOR UPDATE';
        }
        $slotID = $this->fetchRow(
            $sql,
            $data
        );
        return $slotID ? $slotID['slotID'] : false ;
    }

    /**
     * Lock all slots required for an appointment in a single SELECT ... FOR UPDATE.
     * Uses ORDER BY slotID ASC so lock acquisition prefers primary-key order.
     */
    protected function lockSlotsForAppointment(
        Collection $slotList,
        AvailabilityEntity $availability,
        \DateTimeInterface $date
    ): void {
        $times = [];
        foreach ($slotList as $slot) {
            if (!$slot instanceof Entity) {
                continue;
            }
            $times[] = $slot->getTimeString();
        }
        $times = array_values(array_unique($times));
        if ($times === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($times), '?'));
        $sql = sprintf(
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_SLOTS_FOR_UPDATE,
            $placeholders
        );
        $params = array_merge(
            [
                $availability['scope']['id'],
                $availability['id'],
                $date->format('Y'),
                $date->format('m'),
                $date->format('d'),
            ],
            $times
        );
        $this->fetchAll($sql, $params);
    }

    /**
     * @return null|true
     */
    public function hasScopeRelevantChanges(
        \BO\Zmsentities\Scope $scope,
        ?\DateTimeInterface $slotLastChange = null
    ) {
        $startInDaysDefault = (new \BO\Zmsbackend\Preferences\Service\Preferences())
            ->readProperty('scope', $scope->id, 'appointment', 'startInDaysDefault');
        $endInDaysDefault = (new \BO\Zmsbackend\Preferences\Service\Preferences())
            ->readProperty('scope', $scope->id, 'appointment', 'endInDaysDefault');
        if (
            $scope->preferences['appointment']['startInDaysDefault'] != $startInDaysDefault
            || $scope->preferences['appointment']['endInDaysDefault'] != $endInDaysDefault
        ) {
            (new \BO\Zmsbackend\Scope\Service\Scope())->replacePreferences($scope); //TODO remove after ZMS1 is deactivated
            return true;
        }
        $startInDaysChange = (new \BO\Zmsbackend\Preferences\Service\Preferences())
            ->readChangeDateTime('scope', $scope->id, 'appointment', 'startInDaysDefault');
        $endInDaysChange = (new \BO\Zmsbackend\Preferences\Service\Preferences())
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
        ?\DateTimeInterface $slotLastChange = null,
        int $oldestSlotVersion = 1
    ): bool {
        $proposedChange = new \BO\Zmsbackend\Helper\AvailabilitySnapShot($availability, $now);
        $formerChange = new \BO\Zmsbackend\Helper\AvailabilitySnapShot($availability, $slotLastChange);

        if ($availability->version > $oldestSlotVersion) {
            $availability['processingNote'][] = 'outdated: availability version change';
            return true;
        }

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
        ?\DateTimeInterface $slotLastChange = null
    ) {
        $now = \BO\Zmsentities\Helper\DateTime::create($now);
        $calculateSlotsUntilDate = \BO\Zmsentities\Helper\DateTime::create($now)->modify('+' . self::MAX_DAYS_OF_SLOT_CALCULATION . ' days');
        if (!$slotLastChange) {
            $slotLastChange = $this->readLastChangedTimeByAvailability($availability);
        }
        $lastGeneratedSlotDate = $this->getLastGeneratedSlotDate($availability);
        $oldestSlotVersion = $this->getOldestSlotVersionByAvailability($availability);
        $availability['processingNote'][] = 'lastchange=' . $slotLastChange->format('c');
        if (!$this->isAvailabilityOutdated($availability, $now, $slotLastChange, $oldestSlotVersion)) {
            return false;
        }
        $startDate = $availability->getBookableStart($now)->modify('00:00:00');
        $stopDate = $availability->getBookableEnd($now);
        $generateNew = empty($lastGeneratedSlotDate) || $availability->version > $oldestSlotVersion;
        (new \BO\Zmsbackend\Availability\Service\Availability())->readLock($availability->id);
        $cancelledSlots = 0;
        $cancelledSlots += $this->fetchAffected(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_CANCEL_AVAILABILITY_BEFORE_BOOKABLE, [
            'availabilityID' => $availability->id,
            'providedDate' => $startDate->format('Y-m-d')
        ]);
        // Cancel slots only if previously generated beyond new bookable end
        if ($lastGeneratedSlotDate && $lastGeneratedSlotDate->getTimestamp() > $stopDate->getTimestamp()) {
            $cancelledSlots += $this->fetchAffected(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_CANCEL_AVAILABILITY_AFTER_BOOKABLE, [
                'availabilityID' => $availability->id,
                'providedDate' => $stopDate->format('Y-m-d')
            ]);
        }
        if ($generateNew) {
            $cancelledSlots += $this->fetchAffected(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_CANCEL_AVAILABILITY, [
                'availabilityID' => $availability->id,
            ]);

            if (!$availability->withData(['bookable' => ['startInDays' => 0]])->hasBookableDates($now)) {
                $availability['processingNote'][] = "cancelled $cancelledSlots slots: availability not bookable ";
                return ($cancelledSlots > 0) ? true : false;
            }

            \App::$log->info('availability: ', [
                'generate_new' => $generateNew,
                'availability_id' => $availability->id,
                'cancelledSlots' => $cancelledSlots
            ]);

            $availability['processingNote'][] = "cancelled $cancelledSlots slots";
        } elseif ($cancelledSlots > 0) {
            $availability['processingNote'][] = "cancelled $cancelledSlots slots";
        }

        $slotlist = $availability->getSlotList();
        $slotlistIntern = $slotlist->withValueFor('public', 0);
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

    public function writeByScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now): \BO\Zmsentities\Collection\AvailabilityList
    {
        $slotLastChange = $this->readLastChangedTimeByScope($scope);
        $availabilityList = (new \BO\Zmsbackend\Availability\Service\Availability())
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

    /**
     * A day used to send one lookup, one slot write, and up to 48 ancestor inserts for every slot.
     * Read the day's slots once, write the slot rows together, then write the ancestor rows together.
     * The ancestor chain is the same: each slot keeps itself and the previous slots, at most 48.
     */
    protected function writeSlotListForDate(
        \DateTimeInterface $time,
        Collection $slotlist,
        AvailabilityEntity $availability
    ) {
        $createdAt = time();
        $rows = [];
        $slotRepository = new \BO\Zmsbackend\Slot\Repository\Slot(\BO\Zmsbackend\Query\Base::INSERT);
        foreach ($slotlist as $slot) {
            $slot = clone $slot;
            $slot->status = 'free';
            $values = $slotRepository->reverseEntityMapping($slot, $availability, $time);
            $values['createTimestamp'] = $createdAt;
            $rows[] = $values;
        }
        if ($rows === []) {
            return false;
        }

        $stored = $this->readSlotIdsForDay($availability, $time);
        $updates = [];
        $inserts = [];
        foreach ($rows as $values) {
            $clock = $this->slotClock((string) $values['time']);
            $packed = [];
            foreach (self::SLOT_WRITE_COLUMNS as $column) {
                $packed[] = $values[$column];
            }
            if (isset($stored[$clock])) {
                array_unshift($packed, $stored[$clock]);
                $updates[] = $packed;
            } else {
                $inserts[] = $packed;
            }
        }
        $this->writeExistingSlotRows($updates);
        $this->writeNewSlotRows($inserts);
        if ($inserts !== []) {
            $stored = $this->readSlotIdsForDay($availability, $time);
            $availability['processingNote'][] = 'Added ' . $time->format('Y-m-d');
        }

        $orderedIds = [];
        foreach ($rows as $values) {
            $clock = $this->slotClock((string) $values['time']);
            if (!isset($stored[$clock])) {
                throw new \RuntimeException('Slot calculation did not store every slot for ' . $time->format('Y-m-d'));
            }
            $orderedIds[] = $stored[$clock];
        }
        $this->replaceAncestorRows($orderedIds);

        return true;
    }

    /**
     * @return array<string, int> clock time => slotID, first row wins when a time is stored twice
     */
    private function readSlotIdsForDay(AvailabilityEntity $availability, \DateTimeInterface $time): array
    {
        $rows = $this->fetchAll(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_SLOTS_FOR_DAY, [
            'scopeID' => $availability->scope->id,
            'availabilityID' => $availability->id,
            'year' => $time->format('Y'),
            'month' => $time->format('m'),
            'day' => $time->format('d'),
        ]);
        $ids = [];
        foreach ($rows as $row) {
            $clock = $this->slotClock((string) $row['time']);
            if (!isset($ids[$clock])) {
                $ids[$clock] = (int) $row['slotID'];
            }
        }

        return $ids;
    }

    private function slotClock(string $time): string
    {
        $parts = explode(':', $time);

        return sprintf('%02d:%02d:%02d', (int) ($parts[0] ?? 0), (int) ($parts[1] ?? 0), (int) ($parts[2] ?? 0));
    }

    /**
     * @param list<list<int|string>> $rows slotID plus SLOT_WRITE_COLUMNS
     */
    private function writeExistingSlotRows(array $rows): void
    {
        if ($rows === []) {
            return;
        }
        $columns = array_merge(['slotID'], self::SLOT_WRITE_COLUMNS);
        $assignments = [];
        foreach (self::SLOT_WRITE_COLUMNS as $column) {
            $assignments[] = sprintf('`%s` = VALUES(`%s`)', $column, $column);
        }
        $this->writeChunks(
            'INSERT INTO `slot` (' . $this->quotedColumns($columns) . ') VALUES ',
            ' ON DUPLICATE KEY UPDATE ' . implode(', ', $assignments),
            $rows
        );
    }

    /**
     * @param list<list<int|string>> $rows SLOT_WRITE_COLUMNS, without slotID
     */
    private function writeNewSlotRows(array $rows): void
    {
        if ($rows === []) {
            return;
        }
        $this->writeChunks(
            'INSERT INTO `slot` (' . $this->quotedColumns(self::SLOT_WRITE_COLUMNS) . ') VALUES ',
            '',
            $rows
        );
    }

    /**
     * @param list<int> $slotIds chronological slot ids for one day
     */
    private function replaceAncestorRows(array $slotIds): void
    {
        if ($slotIds === []) {
            return;
        }
        foreach (array_chunk($slotIds, 500) as $chunk) {
            $marks = implode(',', array_fill(0, count($chunk), '?'));
            $this->perform('DELETE FROM `slot_hiera` WHERE `slotID` IN (' . $marks . ')', $chunk);
        }
        $rows = $this->ancestorRows($slotIds);
        if ($rows === []) {
            return;
        }
        $this->writeChunks(
            'INSERT INTO `slot_hiera` (`slotID`, `ancestorID`, `ancestorLevel`) VALUES ',
            '',
            $rows
        );
    }

    /**
     * Same pairs as the old per-slot loop: level 1 is the slot itself, higher levels are earlier slots, capped at 48.
     *
     * @param list<int> $slotIds
     * @return list<array{0: int, 1: int, 2: int}>
     */
    private function ancestorRows(array $slotIds): array
    {
        $rows = [];
        $count = count($slotIds);
        for ($index = 0; $index < $count; $index++) {
            $level = $index + 1;
            for ($ancestor = 0; $ancestor <= $index; $ancestor++) {
                if ($level <= self::MAX_SLOTS) {
                    $rows[] = [$slotIds[$index], $slotIds[$ancestor], $level];
                }
                $level--;
            }
        }

        return $rows;
    }

    /**
     * @param list<string> $columns
     */
    private function quotedColumns(array $columns): string
    {
        return implode(',', array_map(static fn (string $column): string => '`' . $column . '`', $columns));
    }

    /**
     * @param list<list<int|string>> $rows
     */
    private function writeChunks(string $sql, string $suffix, array $rows): void
    {
        foreach (array_chunk($rows, 200) as $chunk) {
            $width = count($chunk[0]);
            $tuple = '(' . implode(',', array_fill(0, $width, '?')) . ')';
            $params = [];
            foreach ($chunk as $row) {
                foreach ($row as $value) {
                    $params[] = $value;
                }
            }
            $this->perform($sql . implode(',', array_fill(0, count($chunk), $tuple)) . $suffix, $params);
        }
    }

    public function readLastChangedTime(): \DateTimeImmutable
    {
        $last = $this->fetchRow(
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_LAST_CHANGED
        );
        if (!$last['dateString']) {
            $last['dateString'] = '1970-01-01 12:00';
        }
        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsbackend\Connection\Select::$connectionTimezone);
    }

    public function readLastChangedTimeByScope(ScopeEntity $scope): \DateTimeImmutable
    {
        $last = $this->fetchRow(
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_LAST_CHANGED_SCOPE,
            [
                'scopeID' => $scope->id,
            ]
        );
        if (!$last['dateString']) {
            $last['dateString'] = '1970-01-01 12:00';
        }
        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsbackend\Connection\Select::$connectionTimezone);
    }

    public function readLastChangedTimeByAvailability(AvailabilityEntity $availabiliy): \DateTimeImmutable
    {
        $last = $this->fetchRow(
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_LAST_CHANGED_AVAILABILITY,
            [
                'availabilityID' => $availabiliy->id,
            ]
        );
        if (!$last['dateString']) {
            $last['dateString'] = '1970-01-01 12:00';
        }
        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsbackend\Connection\Select::$connectionTimezone);
    }

    public function updateSlotProcessMapping($scopeID = null): int
    {
        if ($scopeID) {
            $processIdList = $this->fetchAll(
                \BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_MISSING_PROCESS
                . \BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_MISSING_PROCESS_BY_SCOPE,
                ['scopeID' => $scopeID]
            );
        } else {
            $processIdList = $this->fetchAll(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_MISSING_PROCESS, []);
        }
        // Client side INSERT ... SELECT ... to reduce table locking
        foreach ($processIdList as $processId) {
            $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_INSERT_SLOT_PROCESS, array_values($processId));
        }
        return count($processIdList);
    }

    public function deleteSlotProcessOnSlot($scopeID = null): void
    {
        if ($scopeID) {
            $this->perform(
                \BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED
                . \BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED_BY_SCOPE,
                ['scopeID' => $scopeID]
            );
        } else {
            $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_PROCESS_CANCELLED, []);
        }
    }

    public function deleteSlotProcessOnProcess($scopeID = null): int
    {
        if ($scopeID) {
            $processIdList = $this->fetchAll(
                \BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS
                . \BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS_BY_SCOPE,
                ['scopeID' => $scopeID]
            );
        } else {
            $processIdList = $this->fetchAll(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_DELETABLE_SLOT_PROCESS);
        }
        // Client side INSERT ... SELECT ... to reduce table locking
        foreach ($processIdList as $processId) {
            $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_PROCESS_ID, $processId);
        }
        return count($processIdList);
    }

    public function writeSlotProcessMappingFor($processId): static
    {
        $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_INSERT_SLOT_PROCESS_ID, [
            'processId' => $processId,
        ]);
        return $this;
    }

    public function deleteSlotProcessMappingFor($processId): static
    {
        $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_PROCESS_ID, [
            'processId' => $processId,
        ]);
        return $this;
    }

    public function writeCanceledByTimeAndScope(\DateTimeInterface $dateTime, \BO\Zmsentities\Scope $scope): bool
    {
        $status = $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_UPDATE_SLOT_MISSING_AVAILABILITY_BY_SCOPE, [
            'dateString' => $dateTime->format('Y-m-d'),
            'scopeID' => $scope->id,
        ]);

        return $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_CANCEL_SLOT_OLD_BY_SCOPE, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
            'time' => $dateTime->format('H:i:s'),
            'scopeID' => $scope->id,
        ]) && $status;
    }

    public function writeCanceledByTime(\DateTimeInterface $dateTime): bool
    {
        $status = $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_UPDATE_SLOT_MISSING_AVAILABILITY, [
            'dateString' => $dateTime->format('Y-m-d'),
        ]);
        return $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_CANCEL_SLOT_OLD, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
            'time' => $dateTime->format('H:i:s'),
        ]) && $status;
    }

    public function deleteSlotsOlderThan(\DateTimeInterface $dateTime): bool
    {
        $status = $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_OLD, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
        ]);
        $status = ($status && $this->perform(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_DELETE_SLOT_HIERA));
        return $status;
    }

    /**
     * This function is for debugging
     */
    public function readRowsByScopeAndDate(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $dateTime
    ) {
        $list = $this->fetchAll(\BO\Zmsbackend\Slot\Repository\Slot::QUERY_SELECT_BY_SCOPE_AND_DAY, [
            'year' => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day' => $dateTime->format('d'),
            'scopeID' => $scope->id,
        ]);
        return $list;
    }

    public function writeOptimizedSlotTables(): bool
    {
        $queries = [
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_OPTIMIZE_SLOT,
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_OPTIMIZE_SLOT_HIERA,
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_OPTIMIZE_SLOT_PROCESS,
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_OPTIMIZE_PROCESS,
        ];

        $status = true;
        foreach ($queries as $query) {
            try {
                $status = $status && $this->perform($query);
            } catch (\PDOException $e) {
                \App::$log->error("Failed to optimize table with query: $query. Error: " . $e->getMessage(), []);

                return false;
            }
        }

        return $status;
    }

    private function getLastGeneratedSlotDate(AvailabilityEntity $availability): \DateTimeImmutable|null
    {
        $last = $this->fetchRow(
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_LAST_IN_AVAILABILITY,
            [
                'availabilityID' => $availability->id,
            ]
        );

        if (!isset($last['dateString'])) {
            return null;
        }

        return new \DateTimeImmutable($last['dateString'] . \BO\Zmsbackend\Connection\Select::$connectionTimezone);
    }

    private function getOldestSlotVersionByAvailability(AvailabilityEntity $availability)
    {
        $last = $this->fetchRow(
            \BO\Zmsbackend\Slot\Repository\Slot::QUERY_OLDEST_VERSION_IN_AVAILABILITY,
            [
                'availabilityID' => $availability->id,
            ]
        );

        return $last['version'] ?? 1;
    }
}
