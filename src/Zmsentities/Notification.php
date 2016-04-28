<?php

namespace BO\Zmsentities;

class Notification extends Schema\Entity
{
    public static $schema = "notification.json";

    public function getScopeId()
    {
        return (\array_key_exists('id', $this->process['scope'])) ? $this->process['scope']['id'] : null;
    }

    public function getProcessId()
    {
        return (\array_key_exists('id', $this->process)) ? $this->process['id'] : null;
    }

    public function getProcessAuthKey()
    {
        return (\array_key_exists('authKey', $this->process)) ? $this->process['authKey'] : null;
    }

    public function getDepartmentId()
    {
        return (\array_key_exists('id', $this->department)) ? $this->department['id'] : null;
    }

    public function addScope($scope)
    {
        $this->process['scope'] = $scope;
        return $this;
    }
}
