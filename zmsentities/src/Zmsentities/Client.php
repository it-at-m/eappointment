<?php

namespace BO\Zmsentities;

class Client extends Schema\Entity
{
    public static string $schema = "client.json";

    /**
     * @return (false|int|string)[]
     *
     * @psalm-return array{familyName: '', email: '', emailSendCount: 0, surveyAccepted: false, telephone: ''}
     */
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

    public function hasFamilyName(): bool
    {
        return ($this->toProperty()->familyName->get()) ? true : false;
    }

    public function hasEmail(): bool
    {
        return ($this->toProperty()->email->get()) ? true : false;
    }

    public function hasTelephone(): bool
    {
        return ($this->toProperty()->telephone->get()) ? true : false;
    }

    public function getEmailSendCount()
    {
        return $this->toProperty()->emailSendCount->get();
    }

    public function hasSurveyAccepted(): bool
    {
        return (1 == $this->toProperty()->surveyAccepted->get()) ? true : false;
    }
}
