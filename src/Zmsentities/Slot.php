<?php

namespace BO\Zmsentities;

class Slot extends Schema\Entity
{
    public static $schema = "slot.json";

    public function getDefaults()
    {
        return [
            'public' => 0,
            'intern' => 0,
            'callcenter' => 0,
        ];
    }

    public function setSlotData($workstationCount, Helper\DateTime $slotTime = null)
    {
        $this->time = (null !== $slotTime) ? $slotTime->format('H:i') : null;
        $this->public += $workstationCount['public'];
        $this->callcenter += $workstationCount['callcenter'];
        $this->intern += $workstationCount['intern'];
        return $this;
    }

    public function hasTime()
    {
        return $this->time ? true : false;
    }
}
