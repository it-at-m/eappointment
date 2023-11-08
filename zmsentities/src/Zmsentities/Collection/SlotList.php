<?php
namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Slot;

/**
 * @SuppressWarnings(Complexity)
 */
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
    public function withSlotsForAppointment(\BO\Zmsentities\Appointment $appointment, $extendSlotList = false)
    {
        $slotList = new SlotList();
        $takeFollowingSlot = 0;
        $startTime = $appointment->toDateTime();
        $currentSlot = null;
        foreach ($this as $slot) {
            $currentSlot = clone $slot;
            if ($takeFollowingSlot > 0) {
                $takeFollowingSlot--;
                $slotList[] = $currentSlot;
            }
            if ($slot->hasTime() && $slot->time == $startTime->format('H:i')) {
                $slotList[] = $currentSlot;
                $takeFollowingSlot = $appointment['slotCount'] - 1;
            }
        }
        if (0 < $takeFollowingSlot && ! $extendSlotList) {
            throw new \BO\Zmsentities\Exception\AppointmentNotFitInSlotList(
                "$appointment does not fit in $this"
            );
        }
        if (0 < $takeFollowingSlot && $extendSlotList) {
            $slotList = $this->extendList($slotList, $currentSlot, $appointment);
        }
        return $slotList;
    }

    public function extendList($slotList, $prevSlot, $appointment)
    {
        $startTime = \BO\Zmsentities\Helper\DateTime::create(
            $appointment->toDateTime()->format('Y-m-d') .' '. $prevSlot->time
        )->modify('+' . $appointment->getAvailability()->slotTimeInMinutes . 'minute');
        $stopTime = $appointment->getEndTime();
        do {
            $slot = clone $prevSlot;
            $slot->setTime($startTime);
            $slotList[] = $slot;
            $startTime = $startTime->modify('+' . $appointment->getAvailability()->slotTimeInMinutes . 'minute');
            // Only add a slot, if at least a minute is left, otherwise do not ("<" instead "<=")
        } while ($startTime->getTimestamp() < $stopTime->getTimestamp());
        return $slotList;
    }
    

    /**
     * Reduce free appointments on slot matching appointment
     *
     * -----------|----------------|------------
     * first loop | intern slots 2 | slotCount 3
     *
     *
     * @return bool true on success and false if no matching slot is found or no appointments are free
     */


    public function removeAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $takeFollowingSlot = 0;
        $startTime = $appointment->toDateTime()->format('H:i');
        $containsAppointment = false;
        //error_log('check: ' . $appointment);
        foreach ($this as $slot) {
            if ($takeFollowingSlot > 0) {
                if (0 == $slot['intern']) {
                    //error_log('false 2: ' . $slot);
                    return false;
                }
                $slot->removeAppointment();
                $takeFollowingSlot--;
                //error_log('intern 2: ' . $slot . ' | following: ' . $takeFollowingSlot);
            }
            if ($slot->hasTime() && $slot->time == $startTime) {
                $takeFollowingSlot = $appointment['slotCount'] - 1;
                //error_log('intern: ' . $slot . ' | following: ' . $takeFollowingSlot);
                if (0 < $slot['intern']) {
                    $containsAppointment = true;
                    $slot->removeAppointment();
                } else {
                    //error_log('false: ' . $slot);
                    return false;
                }
            }
        }
        return $containsAppointment;
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
