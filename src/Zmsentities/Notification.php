<?php

namespace BO\Zmsentities;

class Notification extends Schema\Entity
{
    const PRIMARY = 'id';

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

    public function isEncoding()
    {
        return (\base64_decode($this->message, true)) ? true : false;
    }

    public function getMessage()
    {
        return ($this->isEncoding()) ? \base64_decode($this->message) : $this->message;
    }

    public function getIdentification()
    {
        return $this->department['preferences']['notifications']['identification'];
    }

    public function toResolvedEntity(Process $process, Config $config)
    {
        $entity = clone $this;
        $entity->process = $process;
        $entity->message = Helper\Messaging::getNotificationContent($process, $config);
        $entity->createIP = $process->createIP;
        $entity->department = $process['scope']['department'];
        return $entity;
    }

    public function hasProperties()
    {
        $result = true;
        $requiredProperties = func_get_args();
        foreach ($requiredProperties as $property) {
            if (!array_key_exists($property, $this)) {
                throw new Exception\NotificationMissingProperties("Missing property $property");
                $result = false;
            }
        }
        return $result;
    }
}
