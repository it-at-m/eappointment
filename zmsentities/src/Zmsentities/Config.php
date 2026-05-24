<?php

namespace BO\Zmsentities;

class Config extends Schema\Entity
{
    public static $schema = "config.json";

    public function getDefaults()
    {
        return [
            'appointments' => [
                'urlChange' => 'https://service.berlin.de/terminvereinbarung/termin/manage/',
                'urlAppointments' => 'https://service.berlin.de/terminvereinbarung/',
            ],
            'ticketprinter' => [
                'baseUrl' => '/terminvereinbarung/ticketprinter/'
            ],
            'calldisplay' => [
                'baseUrl' => '/terminvereinbarung/calldisplay/'
            ],
            'emergency' => [
                'refreshInterval' => 5
            ],
            'support' => [
                'eMail' => 'hotline@itdz-berlin.de',
                'telephone' => '(030) 9222-2000'
            ]
        ];
    }

    public function hasType($type)
    {
        return (isset($this[$type])) ? true : false;
    }

    public function hasPreference($type, $key)
    {
        return ($this->hasType($type) && isset($this[$type][$key])) ? true : false;
    }

    public function getPreference($type, $key)
    {
        return $this->toProperty()->$type->$key->get();
    }

    public function setPreference($type, $key, $value)
    {
        $preference = $this->toProperty()->$type->$key->get();
        if (null !== $preference) {
            $this[$type][$key] = $value;
        }
        return $this;
    }
}
