<?php

namespace BO\Zmsentities;

class Appointmenttypes extends Schema\Entity
{
    public static $schema = "appointmenttypes.json";

    public function getDefaults()
    {
        return [
            'public' => 0,
            'intern' => 0,
            'callcenter' => 0,
        ];
    }
}
