<?php

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\Property;

class Notification extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static $schema = "notification.json";

    public function getScopeId()
    {
        return $this->toProperty()->process->scope->id->get();
    }

    public function getProcessId()
    {
        return $this->toProperty()->process->id->get();
    }

    public function getProcessAuthKey()
    {
        return $this->toProperty()->process->authKey->get();
    }

    public function getProcess()
    {
        if (!isset($this['process'])) {
            $this->process = new Process();
        } elseif (!$this->process instanceof Process) {
            $this->process = new Process($this->process);
        }
        return $this->process;
    }

    public function getDepartmentId()
    {
        return $this->toProperty()->department->id->get();
    }

    public function addScope($scope)
    {
        $this->process['scope'] = $scope;
        return $this;
    }

    public function getClient()
    {
        if (!isset($this['client'])) {
            $this->client = new Client();
        } elseif (!$this->client instanceof Client) {
            $this->client = new Client($this->client);
        }
        return $this->client;
    }

    public function getCreateDateTime($timezone = 'Europe/Berlin')
    {
        $dateTime = (new Helper\DateTime())->setTimestamp($this->createTimestamp);
        if ($dateTime) {
            $dateTime = $dateTime->setTimeZone(new \DateTimeZone($timezone));
        }
        return $dateTime;
    }

    public function getFirstClient()
    {
        $client = null;
        if ($this->toProperty()->process->isAvailable()) {
            $process = new Process($this->process);
            $client = $process->getFirstClient();
        }
        return $client;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function hasContent()
    {
        return ('' != $this->getMessage());
    }

    public function getIdentification()
    {
        return $this->toProperty()->department->preferences->notifications->identification->get();
    }

    public function getRecipient()
    {
        if (
            ! isset($this->client['telephone'])
            || "" == $this->client['telephone']
            || strlen($this->client['telephone']) < 7
        ) {
            throw new Exception\NotificationMissedNumber();
        }
        $telephone = $this->getParsedTelephoneNumber();
        $recipient = 'SMS=' . $telephone . '@sms.verwalt-berlin.de';
        return $recipient;
    }

    public function getParsedTelephoneNumber($number = null)
    {
        $number = ($number) ? $number : $this->client['telephone'];
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($number, 'DE');
        return '+' . $phoneNumberObject->getCountryCode() . $phoneNumberObject->getNationalNumber();
    }

    public function toResolvedEntity(Process $process, Config $config, Department $department, $status)
    {
        $entity = clone $this;
        $entity->process = $process;
        $entity->message = Helper\Messaging::getNotificationContent($process, $config, $status);
        $entity->createIP = $process->createIP;
        $entity->department = $department;
        if (! isset($entity['client'])) {
            $entity['client'] = $entity->getFirstClient();
        }
        return $entity;
    }

    public function toCustomMessageEntity(Process $process, $collection, Department $department)
    {
        $entity = new self();
        if (
            Property::__keyExists('message', $collection) &&
            '' != $collection['message']->getValue()
        ) {
            $entity->message = html_entity_decode($collection['message']->getValue(), ENT_QUOTES);
        }
        $entity->process = $process;
        $entity->createIP = $process->createIP;
        $entity->department = $department;
        if (! isset($entity['client'])) {
            $entity['client'] = $entity->getFirstClient();
        }
        return $entity;
    }

    public function hasProperties()
    {
        $requiredProperties = func_get_args();
        foreach ($requiredProperties as $property) {
            if (!Property::__keyExists($property, $this)) {
                throw new Exception\NotificationMissedProperty("Missing property $property");
            }
        }
        return true;
    }

    public function __toString()
    {
        $string = "notification#";
        $string .= ($this->hasId()) ? $this->getId() : 0;
        $string .= " recipient:" . $this->getRecipient();
        $string .= " identification:" . $this->getIdentification();
        $string .= " process:" . $this->getProcessId();
        return $string;
    }
}
