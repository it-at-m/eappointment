<?php

namespace BO\Zmsentities;

class Config extends Schema\Entity
{
    public static $schema = "config.json";

    public function getDefaults()
    {
        return [
            'appointments' => [
                'urlChange' => 'https://service-berlin/terminvereinbarung/termin/manage/',
                'urlAppointments' => 'https://service-berlin/terminvereinbarung/',
            ]
        ];
    }
}
