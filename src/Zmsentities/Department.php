<?php

namespace BO\Zmsentities;

class Department extends Schema\Entity
{
    public static $schema = "department.json";

    public function hasNotificationEnabled()
    {
        return ($this->preferences['notifications']['enabled']) ? true : false;
    }

    public function setNotificationPreferences($status = true)
    {
        $this->preferences['notifications']['enabled'] = $status;
        return $this;
    }

    public function getNotificationPreferences()
    {
        return ($this->preferences['notifications']);
    }

    public function toContact($address = array(), $contactPerson = null)
    {
        $address = explode(' ', str_replace(',', '', $address));
        $contact = new Contact();
        $contact['street'] = trim($address[0]);
        $contact['streetNumber'] = trim($address[1]);
        $contact['postalCode'] = trim($address[2]);
        $contact['region'] = trim($address[3]);
        $contact['name'] = $contactPerson;
        $this->contact = $contact;
        return $this->contact;
    }

    public function toAddress()
    {
        $address =
            $this->contact['street'] .' '.
            $this->contact['streetNumber'] .', '.
            $this->contact['postalCode'] .' '.
            $this->contact['region'];
        return $address;
    }

    public function getContactPerson()
    {
        return $this->contact['name'];
    }

    public function getContact()
    {
        return new Contact($this->contact);
    }
}
