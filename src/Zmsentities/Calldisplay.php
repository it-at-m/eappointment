<?php

namespace BO\Zmsentities;

class Calldisplay extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "calldisplay.json";

    public function getDefaults()
    {
        return [
            'serverTime' => time(),
        ];
    }
}
