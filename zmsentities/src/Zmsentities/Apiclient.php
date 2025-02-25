<?php

namespace BO\Zmsentities;

class Apiclient extends Schema\Entity
{
    public const PRIMARY = 'clientKey';

    public static $schema = "apiclient.json";

    public function getDefaults()
    {
        return [
            'clientKey' => 'wMdVa5Nu1seuCRSJxhKl2M3yw8zqaAilPH2Xc2IZs',
            'shortname' => 'default',
        ];
    }
}
