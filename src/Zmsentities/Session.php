<?php

namespace BO\Zmsentities;

class Session extends Schema\Entity
{
    public static $schema = "session.json";

    public function getDefaults()
    {
        return [
            'id' => '7b89b1c1fd6c7a52fa748ff663babd0c',
            'name' => 'Zmsappointment',
            'content' => array(
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
            )
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
