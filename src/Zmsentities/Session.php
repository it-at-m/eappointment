<?php

namespace BO\Zmsentities;

class Session extends Schema\Entity
{
    public static $schema = "session.json";

    public function getDefaults()
    {
        return [
            'basket' => [
                'requests' => '',
                'providers' => '',
                'scope' => '',
                'process' => '',
                'date' => '',
                'familyName' => '',
                'email' => '',
                'telehone' => '',
                'amendment' => '',
                'authKey' => '',
            ],
            'human' => [
                'captcha_text' => '',
                'Client' => 0,
                'TS' => 0,
                'Origin' => '',
                'RemoteAddress' => '',
                'referrer' => '',
                'Step' => array()
            ],
            'entry' => array(),
            'status' => 'free',
            'task' => '',
            'finished' => false
        ];
    }

    public function getRequests()
    {
        return $this['basket']['requests'];
    }

    public function getProviders()
    {
        return $this['basket']['providers'];
    }
}
