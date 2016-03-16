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
}
