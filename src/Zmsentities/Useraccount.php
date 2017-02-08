<?php

namespace BO\Zmsentities;

class Useraccount extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "useraccount.json";

    public function getDefaults()
    {
        return [
            'rights' => [
                "availability" => false,
                "basic" => true,
                "cluster" => false,
                "department" => false,
                "organisation" => false,
                "scope" => false,
                "sms" => false,
                "superuser" => false,
                "ticketprinter" => false,
                "useraccount" => false,
            ],
        ];
    }

    public function hasProperties()
    {
        foreach (func_get_args() as $property) {
            if (!$this->toProperty()->$property->get()) {
                throw new Exception\UserAccountMissingProperties("Missing property " . htmlspecialchars($property));
                return false;
            }
        }
        return true;
    }

    public function addDepartment($department)
    {
        $this->departments[] = $department;
        return $this;
    }

    public function hasDepartment($departmentId)
    {
        if (count($this->departments)) {
            foreach ($this->departments as $department) {
                if ($department['id'] == $departmentId) {
                    return true;
                }
            }
        }
        return false;
    }

    public function setRights()
    {
        $givenRights = func_get_args();
        foreach ($givenRights as $right) {
            if (array_key_exists($right, $this->rights)) {
                $this->rights[$right] = true;
            }
        }
        return $this;
    }

    public function testRights(array $requiredRights)
    {
        if ($this->hasId()) {
            foreach ($requiredRights as $required) {
                if (!$this->toProperty()->rights->$required->get()) {
                    throw new Exception\UserAccountMissingRights("Missing right " . htmlspecialchars($required));
                }
            }
        } else {
            throw new Exception\UserAccountMissingLogin();
        }
        return $this;
    }

    public function isSuperUser()
    {
        return $this->toProperty()->rights->superuser->get();
    }
}
