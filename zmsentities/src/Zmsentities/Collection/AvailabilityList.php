<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Availability;

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
            $errorList = array_merge($errorList,
                $availability->validateWeekdays($startDate, $endDate, $weekday),
                $availability->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, $kind),
                $availability->validateEndTime($startDate, $endDate),
                $availability->validateOriginEndTime($today, $yesterday, $startDate, $endDate, $selectedDate, $kind),
                $availability->validateType($kind),
                $availability->validateSlotTime($startDate, $endDate),
                $availability->validateBookableDayRange((int)$startInDays, (int)$endInDays)
            );
        }
        return $errorList;
    }

    /**
     * Get the earliest startDateTime and latest endDateTime from an AvailabilityList
     * If the start date of any availability is before the selected date, use the selected date instead.
     *
     * @param AvailabilityList $availabilityList
     * @param \DateTimeImmutable $selectedDate
     * @return array
     */
    public function getDateTimeRangeFromList(\DateTimeImmutable $selectedDate): array
    {
        $earliestStartDateTime = null;
        $latestEndDateTime = null;

        foreach ($this as $availability) {
            // Convert Unix timestamp to date strings
            $startDate = (new \DateTimeImmutable())->setTimestamp($availability->startDate)->format('Y-m-d');
            $endDate = (new \DateTimeImmutable())->setTimestamp($availability->endDate)->format('Y-m-d');

            // Combine date and time for start and end
            $startDateTime = new \DateTimeImmutable("{$startDate} {$availability->startTime}");
            $endDateTime = new \DateTimeImmutable("{$endDate} {$availability->endTime}");

            // Adjust the startDateTime if it's before the selected date
            if ($startDateTime < $selectedDate) {
                $startDateTime = $selectedDate->setTime(0, 0);
            }

            // Determine the earliest start time
            if (is_null($earliestStartDateTime) || $startDateTime < $earliestStartDateTime) {
                $earliestStartDateTime = $startDateTime;
            }

            // Determine the latest end time
            if (is_null($latestEndDateTime) || $endDateTime > $latestEndDateTime) {
                $latestEndDateTime = $endDateTime;
            }
        }

        return [$earliestStartDateTime, $latestEndDateTime];
    }

    public function hasNewVsNewConflicts(\DateTimeImmutable $selectedDate): \BO\Zmsentities\Collection\ProcessList
    {
        $conflicts = new \BO\Zmsentities\Collection\ProcessList();
    
        $newAvailabilities = array_filter(iterator_to_array($this), function ($availability) {
            return isset($availability->tempId);
        });
    
        foreach ($newAvailabilities as $availability1) {
            foreach ($newAvailabilities as $availability2) {
                $scope1Id = is_array($availability1->scope) ? ($availability1->scope['id'] ?? null) : ($availability1->scope->id ?? null);
                $scope2Id = is_array($availability2->scope) ? ($availability2->scope['id'] ?? null) : ($availability2->scope->id ?? null);
    
                if (
                    $availability1 !== $availability2 &&
                    $availability1->type == $availability2->type &&
                    $scope1Id == $scope2Id
                ) {
                    // First check if dates overlap
                    $date1Start = (new \DateTimeImmutable())->setTimestamp($availability1->startDate);
                    $date1End = (new \DateTimeImmutable())->setTimestamp($availability1->endDate);
                    $date2Start = (new \DateTimeImmutable())->setTimestamp($availability2->startDate);
                    $date2End = (new \DateTimeImmutable())->setTimestamp($availability2->endDate);
    
                    // Only check time overlap if the dates overlap
                    if ($date1Start <= $date2End && $date2Start <= $date1End) {
                        $time1Start = strtotime($availability1->startTime);
                        $time1End = strtotime($availability1->endTime);
                        $time2Start = strtotime($availability2->startTime);
                        $time2End = strtotime($availability2->endTime);
    
                        if ($time1Start < $time2End && $time2Start < $time1End) {
                            $process = new \BO\Zmsentities\Process();
    
                            $dateRange1 = date('d.m.Y', $availability1->startDate) . ' - ' . date('d.m.Y', $availability1->endDate);
                            $dateRange2 = date('d.m.Y', $availability2->startDate) . ' - ' . date('d.m.Y', $availability2->endDate);
                            $timeRange1 = date('H:i', $time1Start) . ' - ' . date('H:i', $time1End);
                            $timeRange2 = date('H:i', $time2Start) . ' - ' . date('H:i', $time2End);
    
                            $process->amendment = "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n"
                                . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange1, $timeRange1, Wochentag(e): " . $availability1->getWeekdayNames() . "]\n"
                                . "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[$dateRange2, $timeRange2, Wochentag(e): " . $availability2->getWeekdayNames() . "]";
    
                            $appointment = new \BO\Zmsentities\Appointment();
                            $appointment->date = $availability1->startDate;
                            $appointment->availability = $availability1;
                            $process->addAppointment($appointment);
                            $conflicts->addEntity($process);
                        }
                    }
                }
            }
        }
        return $conflicts;
    }

    public function checkAllVsExistingConflicts($startDate, $endDate)
    {
        $processList = new ProcessList();
        foreach ($this as $availability) {
            $conflict = $availability->getConflict();
            $currentDate = $startDate;
            while ($currentDate <= $endDate) {
                if ($availability->isOpenedOnDate($currentDate)) {
                    if ($conflict) {
                        $conflictOnDay = clone $conflict;
                        // to avoid overwrite time settings from availability getConflict lets modify
                        $appointmentTime = $conflictOnDay->getFirstAppointment()->getStartTime()->format('H:i');
                        $newDate = clone $currentDate;
                        $conflictOnDay->getFirstAppointment()->setDateTime($newDate->modify($appointmentTime));
                        $processList->addEntity($conflictOnDay);
                    }
                    $overlapList = $this->hasOverlapWith($availability, $currentDate);
                    if ($overlapList->count()) {
                        $processList->addList($overlapList);
                    }
                }
                $currentDate = $currentDate->modify('+1day');
            }
        }
        return $processList;
    }

    public function hasOverlapWith(Availability $availability, \DateTimeInterface $currentDate)
    {
        $processList = new ProcessList();
        foreach ($this as $availabilityCompare) {
            if ($availabilityCompare->isOpenedOnDate($currentDate)) {
                $overlaps = $availability->getTimeOverlaps($availabilityCompare, $currentDate);
                $processList->addList($overlaps);
            }
        }
        return $processList;
    }

    /**
     * @return integer
     */
    public function getSummerizedSlotCount()
    {
        return array_reduce($this->getArrayCopy(), function ($carry, $item) {
            $itemId = ($item->id) ? $item->id : $item->tempId;
            $maxSlots = (int) $item->getSlotList()->getSummerizedSlot()->intern;
            $carry[$itemId] = $maxSlots;
            return $carry;
        }, []);
    }

    /**
     * @return integer
     */
    public function getCalculatedSlotCount(\BO\Zmsentities\Collection\ProcessList $processList)
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
}
