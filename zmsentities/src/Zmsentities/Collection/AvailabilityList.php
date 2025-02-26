<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\ProcessList;

/**
 * @SuppressWarnings(Complexity)
 */
class AvailabilityList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Availability';

    public function getMaxWorkstationCount()
    {
        $max = 0;
        foreach ($this as $availability) {
            if ($availability['workstationCount']['intern'] > $max) {
                $max = $availability['workstationCount']['intern'];
            }
        }
        return $max;
    }

    public function withCalculatedSlots()
    {
        $list = clone $this;
        foreach ($list as $key => $availability) {
            $list[$key] = $availability->withCalculatedSlots();
        }
        return $list;
    }

    public function withType($type)
    {
        $collection = new static();
        foreach ($this as $availability) {
            if ($availability->type == $type) {
                $collection[] = clone $availability;
            }
        }
        return $collection;
    }

    public function withOutDoubles()
    {
        $collection = new static();
        foreach ($this as $availability) {
            if (false === $collection->hasMatchOf($availability)) {
                $collection[] = clone $availability;
            }
        }
        return $collection;
    }

    public function hasMatchOf(Availability $availability)
    {
        foreach ($this as $item) {
            if ($item->isMatchOf($availability)) {
                return $item;
            }
        }
        return false;
    }

    public function withDateTime(\DateTimeInterface $dateTime)
    {
        $list = new self();
        foreach ($this as $availability) {
            if ($availability->isOpenedOnDate($dateTime)) {
                $list->addEntity($availability);
            }
        }
        return $list;
    }

    public function withDateTimeInRange(\DateTimeInterface $startDateTime, \DateTimeInterface $endDateTime)
    {
        $list = new self();
        $currentDateTime = clone $startDateTime;
        while ($currentDateTime <= $endDateTime) {
            foreach ($this as $availability) {
                if ($availability->isOpenedOnDate($currentDateTime)) {
                    $list->addEntity($availability);
                }
            }
            $currentDateTime = $currentDateTime->modify('+1 day');
        }
        return $list->withOutDoubles();
    }

    public function getAvailableSecondsOnDateTime(\DateTimeInterface $dateTime, $type = "intern")
    {
        $seconds = 0;
        foreach ($this->withType('appointment')->withDateTime($dateTime) as $availability) {
            $seconds += $availability->getAvailableSecondsPerDay($type);
        }
        return $seconds;
    }

    /*
     * is opened on a day -> not specified by a time
     */
    public function isOpenedByDate(\DateTimeInterface $dateTime, $type = false)
    {
        foreach ($this as $availability) {
            if ($availability->isOpenedOnDate($dateTime, $type)) {
                return true;
            }
        }
        return false;
    }

    /*
     * is opened on a day with specified time
     */
    public function isOpened(\DateTimeInterface $dateTime, $type = "openinghours")
    {
        foreach ($this as $availability) {
            if ($availability->isOpened($dateTime, $type)) {
                return true;
            }
        }
        return false;
    }

    public function hasAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        foreach ($this as $availability) {
            if ($availability->hasAppointment($appointment)) {
                return true;
            }
        }
        return false;
    }

    public function getSlotList()
    {
        $slotList = new SlotList();
        foreach ($this as $availability) {
            foreach ($availability->getSlotList() as $slot) {
                $slotList->addEntity($slot);
            }
        }
        return $slotList;
    }

    public function getSlotListByType($type)
    {
        $slotList = new SlotList();
        foreach ($this as $availability) {
            if ($availability->type == $type) {
                foreach ($availability->getSlotList() as $slot) {
                    $slotList->addEntity($slot);
                }
            }
        }
        return $slotList;
    }

    public function validateInputs(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        \DateTimeImmutable $selectedDate,
        string $kind,
        $startInDays,
        $endInDays,
        array $weekday
    ): array {
        $errorList = [];

        $today = new \DateTimeImmutable();
        $yesterday = $selectedDate->modify('-1 day');
        $tomorrow = $selectedDate->modify('+1 day');

        foreach ($this as $availability) {
            $errorList = array_merge(
                $errorList,
                $availability->validateWeekdays($startDate, $endDate, $weekday),
                $availability->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, $kind),
                $availability->validateEndTime($startDate, $endDate),
                $availability->validateOriginEndTime($today, $yesterday, $endDate, $selectedDate, $kind),
                $availability->validateType($kind),
                $availability->validateSlotTime($startDate, $endDate),
                $availability->validateBookableDayRange((int) $startInDays, (int) $endInDays)
            );
        }
        return $errorList;
    }

    public function getDateTimeRangeFromList(): array
    {
        $startDateTime = null;
        $endDateTime = null;

        foreach ($this as $availability) {
            $availabilityStartDateTime = (new \DateTimeImmutable())
                ->setTimestamp($availability->startDate);
            $availabilityEndDateTime = (new \DateTimeImmutable())
                ->setTimestamp($availability->endDate);

            if ($startDateTime === null || $availabilityStartDateTime < $startDateTime) {
                $startDateTime = $availabilityStartDateTime;
            }
            if ($endDateTime === null || $availabilityEndDateTime > $endDateTime) {
                $endDateTime = $availabilityEndDateTime;
            }
        }

        return [$startDateTime, $endDateTime];
    }

    public function getSummerizedSlotCount()
    {
        return array_reduce($this->getArrayCopy(), function ($carry, $item) {
            $itemId = ($item->id) ? $item->id : $item->tempId;
            $maxSlots = (int) $item->getSlotList()->getSummerizedSlot()->intern;
            $carry[$itemId] = $maxSlots;
            return $carry;
        }, []);
    }

    public function getCalculatedSlotCount(ProcessList $processList)
    {
        return array_reduce($this->getArrayCopy(), function ($carry, $item) use ($processList) {
            $itemId = $item->id;
            $listWithAvailability = $processList->withAvailability($item);
            $busySlots = $listWithAvailability->getAppointmentList()->getCalculatedSlotCount();
            $carry[$itemId] = $busySlots;
            return $carry;
        }, []);
    }


    public function withScope(\BO\Zmsentities\Scope $scope)
    {
        $list = clone $this;
        foreach ($list as $key => $availability) {
            $list[$key] = $availability->withScope($scope);
        }
        return $list;
    }

    public function withLessData(array $keepArray = [])
    {
        $list = new self();
        foreach ($this as $availability) {
            $list->addEntity(clone $availability->withLessData($keepArray));
        }
        return $list;
    }

    public function checkForConflictsBetweenNewAvailabilities(): ProcessList
    {
        $conflicts = new ProcessList();

        $newAvailabilities = array_filter(iterator_to_array($this), function ($availability) {
            return isset($availability->tempId) || isset($availability->kind);
        });

        foreach ($newAvailabilities as $availability1) {
            foreach ($newAvailabilities as $availability2) {
                if ($availability1 === $availability2) {
                    continue;
                }

                if (!$this->shouldCompareForConflicts($availability1, $availability2)) {
                    continue;
                }

                if ($this->doAvailabilitiesOverlap($availability1, $availability2)) {
                    $isEqual = $this->areAvailabilityTimesEqual($availability1, $availability2);

                    $conflict1 = new \BO\Zmsentities\Process();
                    $conflict1->status = 'conflict';
                    $appointment1 = new \BO\Zmsentities\Appointment();
                    $appointment1->date = $availability1->startDate;
                    $appointment1->availability = $availability1;
                    $conflict1->addAppointment($appointment1);
                    $conflict1->amendment = $this->createConflictMessage(
                        $availability1,
                        $availability2,
                        true,
                        $isEqual
                    );
                    $conflicts->addEntity($conflict1);

                    $conflict2 = new \BO\Zmsentities\Process();
                    $conflict2->status = 'conflict';
                    $appointment2 = new \BO\Zmsentities\Appointment();
                    $appointment2->date = $availability2->startDate;
                    $appointment2->availability = $availability2;
                    $conflict2->addAppointment($appointment2);
                    $conflict2->amendment = $this->createConflictMessage(
                        $availability2,
                        $availability1,
                        true,
                        $isEqual
                    );
                    $conflicts->addEntity($conflict2);
                }
            }
        }

        return $conflicts;
    }

    private function createConflictMessage(
        Availability $availability1,
        Availability $availability2,
        bool $bothAreNew,
        bool $isEqual
    ): string {
        $dateRange1 = date('d.m.Y', $availability1->startDate) . ' - ' . date('d.m.Y', $availability1->endDate);
        $dateRange2 = date('d.m.Y', $availability2->startDate) . ' - ' . date('d.m.Y', $availability2->endDate);
        $timeRange1 = date('H:i', strtotime($availability1->startTime)) . ' - ' . date('H:i', strtotime($availability1->endTime));
        $timeRange2 = date('H:i', strtotime($availability2->startTime)) . ' - ' . date('H:i', strtotime($availability2->endTime));

        if ($isEqual) {
            $message = "Konflikt: Zwei Öffnungszeiten sind gleich.\n";
        } else {
            $message = "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n";
        }

        if ($bothAreNew) {
            $message .= "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange1, $timeRange1, Wochentag(e): " . $availability1->getWeekdayNames() . "]\n"
                . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange2, $timeRange2, Wochentag(e): " . $availability2->getWeekdayNames() . "]";
        } else {
            $message .= "Bestehende Öffnungszeit:&thinsp;&thinsp;[$dateRange2, $timeRange2, Wochentag(e): " . $availability2->getWeekdayNames() . "]\n"
                . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange1, $timeRange1, Wochentag(e): " . $availability1->getWeekdayNames() . "]";
        }

        return $message;
    }

    public function checkForConflictsWithExistingAvailabilities(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): ProcessList {
        $processList = new ProcessList();
        foreach ($this as $availability) {
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                if ($availability->isOpenedOnDate($currentDate)) {
                    $conflict = $availability->getConflict();
                    if ($conflict) {
                        $conflictOnDay = clone $conflict;
                        $appointmentTime = $conflictOnDay->getFirstAppointment()->getStartTime()->format('H:i');
                        $newDate = clone $currentDate;
                        $conflictOnDay->getFirstAppointment()->setDateTime($newDate->modify($appointmentTime));
                        $processList->addEntity($conflictOnDay);
                    }

                    // Check for overlaps with other availabilities on this date
                    $overlapList = $this->findOverlapsOnDate($availability, $currentDate);
                    if ($overlapList->count()) {
                        $processList->addList($overlapList);
                    }
                }
                $currentDate = $currentDate->modify('+1day');
            }
        }

        return $processList;
    }

    public function findOverlapsOnDate(
        Availability $availability,
        \DateTimeInterface $currentDate
    ): ProcessList {
        $processList = new ProcessList();

        foreach ($this as $availabilityCompare) {
            if ($availability === $availabilityCompare) {
                continue;
            }

            if ($availabilityCompare->isOpenedOnDate($currentDate)) {
                if (!$this->shouldCompareForConflicts($availability, $availabilityCompare)) {
                    continue;
                }

                if ($this->doAvailabilitiesOverlap($availability, $availabilityCompare)) {
                    $isEqual = $this->areAvailabilityTimesEqual($availability, $availabilityCompare);

                    $conflict = new \BO\Zmsentities\Process();
                    $conflict->status = 'conflict';

                    $appointment = new \BO\Zmsentities\Appointment();
                    $appointment->date = $availability->startDate;
                    $appointment->availability = $availability;
                    $conflict->addAppointment($appointment);

                    $conflict->amendment = $this->createConflictMessage(
                        $availability,
                        $availabilityCompare,
                        false,
                        $isEqual
                    );

                    $conflict->getFirstAppointment()->date = $availability
                        ->getStartDateTime()
                        ->modify($currentDate->format("Y-m-d"))
                        ->getTimestamp();

                    $processList->addEntity($conflict);
                }
            }
        }

        return $processList;
    }

    protected function shouldCompareForConflicts(
        Availability $availability1,
        Availability $availability2
    ): bool {
        // Skip if they're not the same type
        if (isset($availability1->type) && isset($availability2->type) && $availability1->type != $availability2->type) {
            return false;
        }

        // Skip if they're not for the same scope (only if both have scope defined)
        if (isset($availability1->scope) && isset($availability2->scope)) {
            $scope1Id = is_array($availability1->scope) ? ($availability1->scope['id'] ?? null) : ($availability1->scope->id ?? null);
            $scope2Id = is_array($availability2->scope) ? ($availability2->scope['id'] ?? null) : ($availability2->scope->id ?? null);

            if ($scope1Id != $scope2Id) {
                return false;
            }
        }

        // Skip if they're part of the same series
        if (
            (isset($availability1->id) && isset($availability2->id) &&
                $availability1->id === $availability2->id) ||
            (isset($availability1->tempId) && isset($availability2->tempId) &&
                $availability1->tempId === $availability2->tempId)
        ) {
            return false;
        }

        return true;
    }

    protected function doAvailabilitiesOverlap(
        Availability $availability1,
        Availability $availability2
    ): bool {
        // Check date overlap
        $date1Start = (new \DateTimeImmutable())->setTimestamp($availability1->startDate);
        $date1End = (new \DateTimeImmutable())->setTimestamp($availability1->endDate);
        $date2Start = (new \DateTimeImmutable())->setTimestamp($availability2->startDate);
        $date2End = (new \DateTimeImmutable())->setTimestamp($availability2->endDate);

        if (!($date1Start <= $date2End && $date2Start <= $date1End)) {
            return false;
        }

        // Check time overlap
        $time1Start = strtotime($availability1->startTime);
        $time1End = strtotime($availability1->endTime);
        $time2Start = strtotime($availability2->startTime);
        $time2End = strtotime($availability2->endTime);

        return ($time1Start < $time2End && $time2Start < $time1End);
    }

    protected function areAvailabilityTimesEqual(
        Availability $availability1,
        Availability $availability2
    ): bool {
        $time1Start = strtotime($availability1->startTime);
        $time1End = strtotime($availability1->endTime);
        $time2Start = strtotime($availability2->startTime);
        $time2End = strtotime($availability2->endTime);

        return ($time1Start === $time2Start && $time1End === $time2End);
    }
}
