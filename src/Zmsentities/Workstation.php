<?php

namespace BO\Zmsentities;

class Workstation extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "workstation.json";

    public function getQueuePreference($key, $isBoolean = false)
    {
        $result = null;
        if (array_key_exists($key, $this['queue'])) {
            if ($isBoolean) {
                $result = ($this['queue'][$key]) ? 1 : 0;
            } else {
                $result = $this['queue'][$key];
            }
        }
        return $result;
    }

    public function getDepartmentById($departmentId)
    {
        $userAccount = new UserAccount($this->useraccount);
        foreach ($userAccount->departments as $department) {
            if ($departmentId == $department['id']) {
                return new Department($department);
            }
        }
        return new Department();
    }

    public function getProviderOfGivenScope()
    {
        return $this->scope['provider']['id'];
    }

    public function getUseraccountRights()
    {
        $rights = null;
        if (array_key_exists('rights', $this->useraccount)) {
            $rights = $this->useraccount['rights'];
        }
        return $rights;
    }

    public function hasSuperUseraccount()
    {
        $isSuperuser = false;
        $userRights = $this->getUseraccountRights();
        if (isset($userRights['superuser'])) {
            $isSuperuser = true;
        }
        return $isSuperuser;
    }

    public function getAuthKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function hasAuthKey()
    {
        return (isset($this->authKey)) ? true : false;
    }
}
