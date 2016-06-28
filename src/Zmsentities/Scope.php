<?php
namespace BO\Zmsentities;

class Scope extends Schema\Entity
{

    public static $schema = "scope.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }

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

    public function hasNotificationEnabled()
    {
        return ($this->preferences['notifications']['enabled']);
    }

    public function getNotificationPreferences()
    {
        return ($this->preferences['notifications']);
    }

    public function getDepartmentId()
    {
        if (array_key_exists('department', $this)) {
            if (array_key_exists('id', $this['department'])) {
                return $this['department']['id'];
            } elseif (array_key_exists('$ref', $this['department'])) {
                $departmentId = preg_replace('#^.*/(\d+)/$#', '$1', $this['department']['$ref']);
                return $departmentId;
            }
        }
        throw new \Exception("No reference to a department found");
    }

    public function getConfirmationContent()
    {
        return ($this->preferences['notifications']['confirmationContent']);
    }

    public function getHeadsUpContent()
    {
        return ($this->preferences['notifications']['headsUpContent']);
    }

    public function getPreference($preferenceKey, $index, $isCheckBox = false)
    {
        if (isset($this->preferences) && array_key_exists($preferenceKey, $this->preferences)) {
            if (array_key_exists($index, $this->preferences[$preferenceKey])) {
                return ($isCheckBox) ? 1 : $this->preferences[$preferenceKey][$index];
            }
        }
        return ($isCheckBox) ? 0 : null;
    }

    public function getStatus($statusKey, $index)
    {
        if (isset($this->status) && array_key_exists($statusKey, $this->status)) {
            if (array_key_exists($index, $this->status[$statusKey])) {
                return $this->status[$statusKey][$index];
            }
        }
        return null;
    }

    public function getContactEmail()
    {
        if (isset($this->contact) && array_key_exists('email', $this->contact)) {
            return $this->contact['email'];
        }
        return null;
    }

    public function getName()
    {
        if (array_key_exists('name', $this->contact)) {
            return $this->contact['name'];
        }
    }
}
