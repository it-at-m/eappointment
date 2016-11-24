<?php
/**
 * @package 115Mandant
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

    public function isOpenedByDate($dateString, $type = 'openinghours')
    {
        $dateTime = \BO\Zmsentities\Helper\DateTime::create($dateString);
        return $this->isOpened($dateTime, $type);
    }

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
