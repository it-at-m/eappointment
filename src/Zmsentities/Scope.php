<?php
namespace BO\Zmsentities;

class Scope extends Schema\Entity
{

    public static $schema = "scope.json";

    public function getProviderId()
    {
        if (array_key_exists('provider', $this)) {
            if (array_key_exists('id', $this['provider'])) {
                return $this['provider']['id'];
            } elseif (array_key_exists('$ref', $this['provider'])) {
                $providerId = preg_replace('#^.*/(\d+)/$#', '$1', $this['provider']['$ref']);
                return $providerId;
            }
        }
        throw new \Exception("No reference to a provider found");
    }

    public function hasNotification()
    {
        return ($this->preferences['notifications']['enabled']);
    }

    public function getNotificationPreferences()
    {
        return ($this->preferences['notifications']);
    }

    public function getDepartmentId()
    {
        return ($this->department['id']);
    }

    public function getConfirmationContent()
    {
        return ($this->preferences['notifications']['confirmationContent']);
    }

    public function getHeadsUpContent()
    {
        return ($this->preferences['notifications']['headsUpContent']);
    }
}
