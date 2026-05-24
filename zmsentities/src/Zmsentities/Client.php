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
            'surveyAccepted' => false,
            'telephone' => ''
        ];
    }

    public function hasFamilyName()
    {
        return ($this->toProperty()->familyName->get()) ? true : false;
    }

    public function hasEmail()
    {
        return ($this->toProperty()->email->get()) ? true : false;
    }

    public function hasTelephone()
    {
        return ($this->toProperty()->telephone->get()) ? true : false;
    }

    public function getEmailSendCount()
    {
        return $this->toProperty()->emailSendCount->get();
    }

    public function hasSurveyAccepted()
    {
        return (1 == $this->toProperty()->surveyAccepted->get()) ? true : false;
    }
}
