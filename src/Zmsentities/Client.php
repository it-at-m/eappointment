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

    public function hasEmail()
    {
        return (!empty($this->email));
    }

    public function hasTelephone()
    {
        return (!empty($this->telephone));
    }

    public function getEmailSendCount()
    {
        return $this->emailSendCount;
    }

    public function getNotificationsSendCount()
    {
        return $this->notificationsSendCount;
    }
}
