<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Availability;

class AvailabilityList extends Base
{
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

    public function withDateTime(\DateTimeImmutable $dateTime)
    {
        $list = new static();
        foreach ($this as $availability) {
            if ($availability->isOpenedOnDate($dateTime)) {
                $list[] = $availability;
            }
        }
        return $list;
    }

    public function getAvailableSecondsOnDateTime(\DateTimeImmutable $dateTime, $type = "intern")
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
    public function isOpenedByDate(\DateTimeImmutable $dateTime, $type = false)
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
    public function isOpened(\DateTimeImmutable $dateTime, $type = "openinghours")
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

    public function getConflicts()
    {
        $processList = new ProcessList();
        $availabilityList = new AvailabilityList();
        foreach ($this as $availability) {
            $conflict = $availability->getConflict();
            if ($conflict) {
                $processList[] = $conflict;
            }
            $overlap = $availabilityList->hasOverlapWith($availability);
            if ($overlap->count()) {
                $processList->addList($overlap);
            } else {
                $availabilityList[] = $availability; // Do not compare entities twice
            }
        }
        return $processList;
    }

    public function hasOverlapWith(Availability $availability)
    {
        $processList = new ProcessList();
        foreach ($this as $availabilityCompare) {
            $overlaps = $availability->getTimeOverlaps($availabilityCompare);
            $processList->addList($overlaps);
        }
        return $processList;
    }
}
