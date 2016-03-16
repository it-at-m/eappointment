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
}
