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
            ],
            'notifications' => [
                'kommandoAbsage' => 'Storno',
                'kommandoAbfrage' => 'Berlin',
                'number' => '0174-8882288',
                'absage' => false,
                'gateway' => 'mail',
                'gatewayUrl' => '',
                'eMailkonfigurierbar' => false,
                'benachrichtigungsfrist' => 10,
                'headsUpContent' => '
                    Sie sind in Kürze an der Reihe. Bitte kommen Sie zum Schalter. Ihre Vorgangsnr. ist
                ',
                'confirmationContent' => '
                    Ihre Telefonnummer wurde erfolgreich registriert. Ihre Wartenr. lautet:
                ',
                'costs' => '0.15',
                'erinnerungsvorlauf' => 180,
            ]
        ];
    }

    public function getNotificationPreferences()
    {
        return $this->toProperty()->notifications->get();
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
