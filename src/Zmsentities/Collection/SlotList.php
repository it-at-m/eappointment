<?php
namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Slot;

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
                $slotA->type = Slot::REDUCED;
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
        $sum = (! $slot) ? new Slot() : new Slot($slot);
        $sum->type = Slot::SUM;
        foreach ($this as $slot) {
            $sum['public'] += $slot['public'];
            $sum['intern'] += $slot['intern'];
            $sum['callcenter'] += $slot['callcenter'];
            if ($sum['type'] != Slot::FREE) {
                $sum['type'] = $slot['type'];
            }
        }
        return $sum;
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
                    throw new \BO\Zmsentities\Exception\SlotMissingTime("Time on slot not set: $slot");
                }
                $appointment->setDateByString($selectedDate .' '. $slot->getTimeString());
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
