<?php

namespace BO\Zmsentities;

class Client extends Schema\Entity
{
    public static $schema = "client.json";

    public function getDefaults()
    {
        return [
            'familyName' => '',
            'email' => '',
            'emailSendCount' => 0,
            'notificationsSendCount' => 0,
            'surveyAccepted' => false,
            'telephone' => ''
        ];
    }
}
