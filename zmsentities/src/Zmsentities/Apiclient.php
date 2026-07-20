<?php

namespace BO\Zmsentities;

class Apiclient extends Schema\Entity
{
    public const PRIMARY = 'clientKey';

    public static $schema = "apiclient.json";

    #[\Override]
    public function getDefaults()
    {
        return [
            'clientKey' => '',
            'shortname' => 'default',
        ];
    }
}
