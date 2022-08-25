<?php

namespace BO\Zmsentities;

use \BO\Zmsentities\Collection\ProcessList;
use \BO\Zmsentities\Helper\Messaging;
use \BO\Zmsentities\Helper\Property;

class Mail extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "mail.json";

    public function getDefaults()
    {
        return [
            'multipart' => new Collection\MimepartList(),
            'process' => new Process(),
            'department' => new Department(),
            'client' => new Client(),
        ];
    }

    public function getProcessId()
    {
        return $this->toProperty()->process->id->get();
    }

    public function getProcessAuthKey()
    {
        return $this->toProperty()->process->authKey->get();
    }

    public function addMultiPart($multiPart)
    {
        $this->multipart = $multiPart;
        return $this;
    }

    public function hasContent()
    {
        return ($this->getHtmlPart() && $this->getPlainPart());
    }

    public function hasIcs()
    {
        return (null != $this->getIcsPart());
    }

    public function getHtmlPart()
    {
        $multiPart = $this->toProperty()->multipart->get();
        foreach ($multiPart as $part) {
            $mimepart = new Mimepart($part);
            if ($mimepart->isHtml()) {
                return $mimepart->getContent();
            }
        }
        return null;
    }

    public function getPlainPart()
    {
        foreach ($this->multipart as $part) {
            $mimepart = new Mimepart($part);
            if ($mimepart->isText()) {
                return $mimepart->getContent();
            }
        }
        return null;
    }

    public function getIcsPart()
    {
        foreach ($this->multipart as $part) {
            $mimepart = new Mimepart($part);
            if ($mimepart->isIcs()) {
                return $mimepart->getContent();
            }
        }
        return null;
    }

    public function getClient(): ?Client
    {
        if (isset($this['client'])) {
            return $this['client'];
        }

        return $this->getFirstClient();
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

    public function toCustomMessageEntity(Process $process, $collection)
    {
        $entity = clone $this;
        $message = '';
        if (Property::__keyExists(
            'message',
            $collection
        ) &&
            '' != $collection['message']->getValue()
        ) {
            $message = $collection['message']->getValue();
        }
        if (Property::__keyExists(
            'subject',
            $collection
        ) &&
            '' != $collection['subject']->getValue()
        ) {
            $entity->subject = $collection['subject']->getValue();
        }
        $entity->process = $process;
        $entity->createIP = $process->createIP;

        if (! isset($entity['client'])) {
            $entity['client'] = $entity->getFirstClient();
        }

        $entity->multipart[] = new Mimepart(array(
            'mime' => 'text/html',
            'content' => $message,
            'base64' => false
        ));
        $entity->multipart[] = new Mimepart(array(
            'mime' => 'text/plain',
            'content' => Messaging::getPlainText($message),
            'base64' => false
        ));
        return $entity;
    }

    /**
     * @param Process|ProcessList Process|ProcessList $processList
     * @param Config $config
     * @param $status
     * @param $initiator
     * @return Mail
     * @throws Exception\TemplateNotFound
     */
    public function toResolvedEntity($processList, Config $config, $status, $initiator = null)
    {
        $collection = static::testProcessList($processList, $status);
        $mainProcess = $collection->getFirst();
        $entity = clone $this;
        $icsRequired = Messaging::isIcsRequired($config, $mainProcess, $status);
        $content = Messaging::getMailContent($collection, $config, $initiator, $status);
        $entity->process = $mainProcess;
        $entity->subject = Messaging::getMailSubject($mainProcess, $config, $initiator, $status);
        $entity->createIP = $mainProcess->createIP;

        if (! isset($entity['client'])) {
            $entity['client'] = $entity->getFirstClient();
        }

        $entity->multipart[] = new Mimepart(array(
            'mime' => 'text/html',
            'content' => $content,
            'base64' => false
        ));
        $entity->multipart[] = new Mimepart(array(
            'mime' => 'text/plain',
            'content' => Messaging::getPlainText($content),
            'base64' => false
        ));
        if ($icsRequired and $mainProcess->getAppointments()->getFirst()->hasTime()) {
            $entity->multipart[] = new Mimepart(array(
                'mime' => 'text/calendar',
                'content' => Messaging::getMailIcs($mainProcess, $config, $status, $initiator)->getContent(),
                'base64' => false
            ));
        }
        return $entity;
    }

    public static function testProcessList($processList, $status)
    {
        $collection = ($processList instanceof Process) ? 
            (new ProcessList())->addEntity($processList) :
            $processList;

        if (0 === $collection->count()) {
            throw new \BO\Zmsentities\Exception\ProcessListEmpty();
        }

        //mainProcess must be first in Collection
        $mainProcess = $collection->getFirst();
        $collection = (1 < $collection->count() && 'overview' != $status) ? 
            $collection->withOutProcessId($mainProcess->getId()) :
            $collection;
        
        return $collection->sortByAppointmentDate();
    }

    public function toScopeAdminProcessList(
        Collection\ProcessList $processList,
        Scope $scope,
        \DateTimeInterface $dateTime
    ) {
        $entity = clone $this;
        $content = Messaging::getScopeAdminProcessListContent($processList, $scope, $dateTime);
        $entity->subject = 'Termine am '. $dateTime->format('Y-m-d');
        $entity->createIP = 0;
        $entity->client = new Client([
            'email' => $scope->getContactEmail(),
            'familyName' => $scope->getName()
        ]);
        $entity->multipart[] = new Mimepart(array(
            'mime' => 'text/html',
            'content' => $content,
            'base64' => false
        ));
        /*
        $entity->multipart[] = new Mimepart(array(
            'mime' => 'text/plain',
            'content' => Messaging::getPlainText($content),
            'base64' => false
        ));
        */
        return $entity;
    }

    public function withDepartment($department)
    {
        $this->department = $department;
        return $this;
    }

    public function getRecipient()
    {
        if (! isset($this['client'])) {
            $this['client'] = $this->getFirstClient();
        }
        if (! isset($this['client']['email']) || "" == $this['client']['email']) {
            throw new Exception\MailMissedAddress();
        }
        return $this['client']['email'];
    }

    public function __toString()
    {
        $string = "mail#";
        $string .= $this->id;
        $string .= " recipient:". $this->getRecipient();
        $string .= " process:". $this->getProcessId();
        return $string;
    }
}
