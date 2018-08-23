<?php
namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Slot;

class SlotList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Slot';

    /**
     * Compare two slots and return the lower values
     * @param array $slotA
     * @param array $slotB
     * @return array $slotA modified
     */
    public function takeLowerSlotValue($indexA, $indexB)
    {
        $slotA = $this[$indexA];
        $slotB = $this[$indexB];
        if (null !== $slotA && null !== $slotB) {
            $slotA->type = Slot::REDUCED;
            foreach (['public', 'intern', 'callcenter'] as $type) {
                $slotA[$type] = $slotA[$type] < $slotB[$type] ? $slotA[$type] : $slotB[$type];
            }
        }
        return $this;
    }

    public function setEmptySlotValues($index)
    {
        $slot = $this->getSlot($index);
        if (null !== $slot) {
            $slot['public'] = 0;
            $slot['intern'] = 0;
            $slot['callcenter'] = 0;
            $slot->type = Slot::REDUCED;
        }
        return $this;
    }

    public function isAvailableForAll($slotType)
    {
        foreach ($this as $slot) {
            if ($slot[$slotType] < 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a slot for a given time
     *
     */
    public function getByDateTime(\DateTimeInterface $dateTime)
    {
        foreach ($this as $slot) {
            if ($slot->hasTime() && $slot->time == $dateTime->format('H:i')) {
                return $slot;
            }
        }
        return false;
    }

    /**
     * Get all slots for an appointment
     *
     */
    public function withSlotsForAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $slotList = new SlotList();
        $takeFollowingSlot = 0;
        $startTime = $appointment->toDateTime()->format('H:i');
        foreach ($this as $slot) {
            if ($takeFollowingSlot > 0) {
                $takeFollowingSlot--;
                $slotList[] = $slot;
            }
            if ($slot->hasTime() && $slot->time == $startTime) {
                $slotList[] = $slot;
                $takeFollowingSlot = $appointment['slotCount'] - 1;
            }
        }
        if (0 < $takeFollowingSlot) {
            throw new \BO\Zmsentities\Exception\AppointmentNotFitInSlotList(
                "$appointment does not fit in $this"
            );
        }
        return $slotList;
    }

    /**
     * Reduce free appointments on slot matching appointment
     *
     * @return bool true on success and false if no matching slot is found or no appointments are free
     */
    public function removeAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $slot = $this->getByDateTime($appointment->toDateTime());
        if ($slot && $slot->intern > 0) {
            $slot->removeAppointment();
            return true;
        }
        return false;
    }

    public function getSlot($index)
    {
        $index = intval($index);
        if (!isset($this[$index])) {
            return null;
        }
        return $this[$index];
    }

    public function getSummerizedSlot($slot = null)
    {
        $sum = ($slot instanceof Slot) ? $slot : new Slot();
        $sum->type = Slot::SUM;
        foreach ($this as $slot) {
            //error_log("$slot");
            $sum['public'] += $slot['public'];
            $sum['intern'] += $slot['intern'];
            $sum['callcenter'] += $slot['callcenter'];
        }
        return $sum;
    }

    /*
     * reduce slotlist from slots smaller than reference time (today) + 1800 seconds
     */
    public function withTimeGreaterThan(\DateTimeInterface $dateTime)
    {
        $slotList = clone $this;
        $referenceTime = $dateTime->getTimestamp() + 1800;
        foreach ($this as $index => $slot) {
            $slotTime = \BO\Zmsentities\Helper\DateTime::create(
                $dateTime->format('Y-m-d') .' '. $slot->time
            )->getTimeStamp();
            if ($referenceTime > $slotTime) {
                $slotList->setEmptySlotValues($index);
            }
        }
        return $slotList;
    }

    /**
     * Creates a ProcessList for free processes
     *
     * @param String $selectedDate of format "YYYY-MM-DD"
     * @param \BO\Zmsentities\Scope $scope
     * @param \BO\Zmsentities\Availability $availability
     * @param String $slotType one of "public", "callcenter", "intern"
     * @param Array $requests to add to process
     * @param $slotsRequired Number of slots required
     *
     * @return ProcessList
     */
    public function getFreeProcesses(
        $selectedDate,
        \BO\Zmsentities\Scope $scope,
        \BO\Zmsentities\Availability $availability,
        $slotType,
        $requests,
        $slotsRequired
    ) {
        $processList = new ProcessList();
        foreach ($this as $slot) {
            if ($slotsRequired > 1 && $slot->type != Slot::REDUCED) {
                throw new \BO\Zmsentities\Exception\SlotRequiredWithoutReducing(
                    "With $slotsRequired slots required, "
                    ."do not use SlotList::getFreeProcesses without reduced slots: $slot"
                );
            }
            if ($slot[$slotType] > 0) {
                $appointment = new \BO\Zmsentities\Appointment(array(
                    'scope' => $scope,
                    'availability' => $availability,
                    'slotCount' => $slotsRequired
                ));
                if (!$slot->hasTime()) {
                    throw new \BO\Zmsentities\Exception\SlotMissingTime("Time on slot not set: $slot");
                }
                $appointment->setDateByString($selectedDate .' '. $slot->getTimeString());
                $process = new \BO\Zmsentities\Process(array(
                    'scope' => $scope,
                    'requests' => $requests
                ));
                for ($count = 0; $count < $slot[$slotType]; $count++) {
                    $process->addAppointment($appointment);
                }
                $processList[] = $process;
            }
        }
        return $processList;
    }

    public function withReducedSlots($slotsRequired)
    {
        $slotList = clone $this;
        if ($slotsRequired > 1) {
            $slotLength = count($slotList);
            for ($slotIndex = 0; $slotIndex < $slotLength; $slotIndex ++) {
                if ($slotIndex + $slotsRequired - 1 < $slotLength) {
                    for ($slotRelative = 1; $slotRelative < $slotsRequired; $slotRelative ++) {
                        if ($slotIndex + $slotRelative < $slotLength) {
                            $slotList->takeLowerSlotValue($slotIndex, $slotIndex + $slotRelative);
                        }
                    }
                } else {
                    $slotList->setEmptySlotValues($slotIndex);
                }
            }
        }
        return $slotList;
    }

    public function __toString()
    {
        $count = count($this);
        $sum = $this->getSummerizedSlot();
        return "slotlist#$count ∑$sum";
    }
}
