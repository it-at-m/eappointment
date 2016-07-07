<?php

namespace BO\Zmsentities;

class UserAccount extends Schema\Entity
{
    public static $schema = "useraccount.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
    }

    public function getEncryptedPassword()
    {
        $password = null;
        if (array_key_exists('password', $this)) {
            $password = md5($this->password);
        }
        return $password;
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

    public function addDepartment($departmentId)
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

    public function getRightsLevel()
    {
        $rightsLevel = null;
        foreach ($this->rights as $right => $value) {
            $level = array_search($right, Helper\RightsManager::getPossibleRights(), true);
            if (true === $value && $level > $rightsLevel) {
                $rightsLevel = $level;
            }
        }
        return $rightsLevel;
    }
}
