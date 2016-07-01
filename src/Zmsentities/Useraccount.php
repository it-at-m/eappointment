<?php

namespace BO\Zmsentities;

class Useraccount extends Schema\Entity
{
    public static $schema = "useraccount.json";

    private $allRights = array(
        90 => 'superuser',
        70 => 'organisation',
        50 => 'department',
        40 => 'cluster',
        30 => 'useraccount',
        20 => 'scope',
        15 => 'availability',
        10 => 'ticketprinter',
        0 => 'sms'
    );

    public function getRights()
    {
        if (array_key_exists($this-right, $this->allRights)) {
            return array_slice($this->allRights, $this->right);
        }
        return null;
    }
}
