<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Availability;

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
            if ($availability['workstationCount']['intern'] >  $max) {
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

    public function validateInputs(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        $errorList = [];
        foreach ($this as $availability) {
            // Create DateTimeImmutable objects for today, yesterday, and tomorrow
            $today =  new \DateTime();
            $yesterday = $today->modify('-1 day');
            $tomorrow = $today->modify('+1 day');
            $today = $today->modify('+0 day');
    
            error_log("Today: " . $today->format('Y-m-d H:i:s'));
            error_log("Yesterday: " . $yesterday->format('Y-m-d H:i:s'));
            error_log("Tomorrow: " . $tomorrow->format('Y-m-d H:i:s'));
    
            // Pass DateTimeImmutable objects to validateAll()
            $errorList = array_merge(
                $errorList,
                $availability->validateAll($today, $yesterday, $tomorrow, $startDate)
            );
        }
        return $errorList;
    }
    

    public function getConflicts($startDate, $endDate)
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
