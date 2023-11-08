<?php

namespace BO\Zmsentities;

class Apiclient extends Schema\Entity
{
    const PRIMARY = 'clientKey';

    public static $schema = "apiclient.json";

    public function getDefaults()
    {
        return [
            'shortname' => 'default',
        ];
    }
}
