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

    //@todo freeAppointments could be an array, should be slot entity
    public function __toString()
    {
        $this->freeAppointments = new Slot($this->freeAppointments);
        return "Day @{$this->year}-{$this->month}-{$this->day} with ". $this->freeAppointments;
    }
}
