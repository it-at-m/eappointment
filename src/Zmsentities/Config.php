<?php

namespace BO\Zmsentities;

class Config extends Schema\Entity
{
    public static $schema = "config.json";

    public function getDefaults()
    {
        return [
            'appointments' => [
                'urlChange' => 'http://service-berlin/terminvereinbarung/termin/manage/',
                'urlAppointments' => 'http://service-berlin/terminvereinbarung/',
            ]
        ];
    }
}
