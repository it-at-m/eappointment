<?php

namespace BO\Zmsentities;

class Apiclient extends Schema\Entity
{
    public const PRIMARY = 'clientKey';

    public static $schema = "apiclient.json";

    public function getDefaults()
    {
        return [
            'shortname' => 'default',
        ];
    }
}
