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
                'gatewayUrl' => '
                    http://gateway.mobile-marketing-system.de/send_sms.php?
                    username=KU-CFP5&password=beraterunterwegs&route=route1&text=
                ',
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
        return $this->notifications;
    }

    public function getPreference($type, $key)
    {
        $result = null;
        if (array_key_exists($type, $this)) {
            if (array_key_exists($key, $this[$type])) {
                $result = $this[$type][$key];
            }
        }
        return $result;
    }

    public function setPreference($type, $key, $value)
    {
        if (array_key_exists($type, $this)) {
            if (array_key_exists($key, $this[$type])) {
                $this[$type][$key] = $value;
            }
        }
        return $this;
    }
}
