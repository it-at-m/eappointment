<?php
namespace BO\Zmsentities\Collection;

class SlotList extends Base
{

    /**
     * Compare two slots and return the lower values
     * @param array $slotA
     * @param array $slotB
     * @return array $slotA modified
     */
    public function takeLowerSlotValue($indexA, $indexB)
    {
        $slotA = $this->getSlot($indexA);
        $slotB = $this->getSlot($indexB);
        if (null !== $slotA && null !== $slotB) {
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
            $slot->setSlotData(array(
                'public' => 0,
                'intern' => 0,
                'callcenter' => 0
            ));
        }
        return $this;
    }

    public function getSlot($index)
    {
        $slotKeys = array_keys((array)$this);
        sort($slotKeys);
        if (!isset($slotKeys[$index]) || !isset($this[$slotKeys[$index]])) {
            return null;
        }
        return $this[$slotKeys[$index]];
    }

    public function addSlot(\BO\Zmsentities\Helper\DateTime $startTime, $workstationCount)
    {
        $slot = new \BO\Zmsentities\Slot();
        $slot->setSlotData($workstationCount, $startTime);
        $this[] = $slot;
        return $this;
    }

    public function setSlot($slotnr, \BO\Zmsentities\Helper\DateTime $startTime, $workstationCount)
    {
        $slot = $this->getSlot($slotnr);
        if (null !== $slot) {
            $slot->setSlotData($workstationCount, $startTime);
            $this[$slotnr] = $slot;
        } else {
            throw new \Exception("Slot $slotnr does not exists.");
        }
        return $this;
    }

    public function addFreeAppointments($day, $slotInfo)
    {
        $day['freeAppointments']['public'] += $slotInfo['public'];
        $day['freeAppointments']['intern'] += $slotInfo['intern'];
        $day['freeAppointments']['callcenter'] += $slotInfo['callcenter'];
        return $day;
    }

    public function getFreeProcesses(
        $selectedDate,
        \BO\Zmsentities\Scope $scope,
        \BO\Zmsentities\Availability $availability,
        $slotType,
        $requests
    ) {

        $processList = new ProcessList();
        foreach ($this as $slot) {
            if ($slot[$slotType] > 0) {
                $appointment = new \BO\Zmsentities\Appointment(array(
                    'scope' => $scope,
                    'availability' => $availability,
                    'slotCount' => $slot[$slotType]
                ));
                if (!$slot->hasTime()) {
                    throw new \Exception("Time on slot not set: ".htmlspecialchars(var_export($slot, 1)));
                }
                $appointment->setDateByString($selectedDate .' '. $slot['time']);
                $process = new \BO\Zmsentities\Process(array(
                    'scope' => $scope,
                    'requests' => $requests
                ));
                $process->addAppointment($appointment);
                $processList[] = $process;
            }
        }
        return $processList;
    }
}
