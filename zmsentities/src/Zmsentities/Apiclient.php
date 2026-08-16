<?php

namespace BO\Zmsentities;

class Apiclient extends Schema\Entity
{
    public const string PRIMARY = 'clientKey';

    public static $schema = "apiclient.json";

    /**
     * @return string[]
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'clientKey' => '',
            'shortname' => 'default',
        ];
    }
}
