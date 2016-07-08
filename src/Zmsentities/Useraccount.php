<?php

namespace BO\Zmsentities;

class UserAccount extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "useraccount.json";

    public function hasProperties()
    {
        $result = true;
        $requiredProperties = func_get_args();
        foreach ($requiredProperties as $property) {
            if (!array_key_exists($property, $this)) {
                throw new Exception\UserAccountMissingProperties();
                $result = false;
            }
        }
        return $result;
    }

    public function addDepartmentId($departmentId)
    {
        $this->departments[] = $departmentId;
        return $this;
    }

    public function getDepartmentId()
    {
        $department = array('id' => 0);
        if (count($this->departments)) {
            $department = current($this->departments);
        }
        return $department['id'];
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
                if (!array_key_exists($required, array_filter($this->rights))) {
                    throw new Exception\UserAccountMissingRights();
                }
            }
        } else {
            throw new Exception\UserAccountMissingLogin();
        }
        return $this;
    }
}
