<?php

namespace BO\Zmsentities;

class Day extends Schema\Entity
{
    public static $schema = "day.json";

    public function getDefaults()
    {
        return [
            'year' => '',
            'month' => '',
            'day' => '',
            'freeAppointments' => new Slot(),
        ];
    }

    public function __toString()
    {
        return "Day @{$this->year}-{$this->month}-{$this->day} with " . $this->freeAppointments;
    }
}
