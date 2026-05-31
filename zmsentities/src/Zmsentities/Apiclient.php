<?php

namespace BO\Zmsentities;

class Apiclient extends Schema\Entity
{
    public const PRIMARY = 'clientKey';

    public static string $schema = "apiclient.json";

    /**
     * @return string[]
     *
     * @psalm-return array{clientKey: '', shortname: 'default'}
     */
    public function getDefaults()
    {
        return [
            'clientKey' => '',
            'shortname' => 'default',
        ];
    }
}
