<?php

namespace BO\Zmsentities;

class Mail extends Schema\Entity
{
    public static $schema = "mail.json";

    public function getProcessId()
    {
        return (\array_key_exists('id', $this->process)) ? $this->process['id'] : null;
    }

    public function getProcessAuthKey()
    {
        return (\array_key_exists('authKey', $this->process)) ? $this->process['authKey'] : null;
    }

    public function addMultiPart($multiPart)
    {
        $this->multipart = $multiPart;
        return $this;
    }

    public function getHtmlPart()
    {
        foreach ($this->multipart as $part) {
            $mailpart = new MailPart($part);
            if ($mailpart->isHtml()) {
                return $mailpart->getContent();
            }
        }
        return null;
    }

    public function getPlainPart()
    {
        foreach ($this->multipart as $part) {
            $mailpart = new MailPart($part);
            if ($mailpart->isText()) {
                return $mailpart->getContent();
            }
        }
        return null;
    }

    public function getIcsPart()
    {
        foreach ($this->multipart as $part) {
            $mailpart = new MailPart($part);
            if ($mailpart->isIcs()) {
                return $mailpart->getContent();
            }
        }
        return null;
    }

    public function getFirstClient()
    {
        $client = null;
        if (count($this->process['clients']) > 0) {
            $data = current($this->process['clients']);
            $client = new Client($data);
        }
        return $client;
    }

    public function hasId($itemId)
    {
        return (\array_key_exists('id', $this) && $itemId == $this->id) ? true : false;
    }

    public function toResolvedEntity(Process $process, Config $config)
    {
        $entity = clone $this;
        $content = Helper\Messaging::getMailContent($process, $config);
        $entity->process = $process;
        $entity->subject = Helper\Messaging::getMailSubject($process, $config);
        $entity->createIP = $process->createIP;
        $entity->department = $process->getDepartment();
        $entity->multipart = [
            array(
                'mime' => 'text/html',
                'content' => $content,
                'base64' => false
            ),
            array(
                'mime' => 'text/plain',
                'content' => Helper\Messaging::getPlainText($content),
                'base64' => false
            ),
            array(
                'mime' => 'text/calendar',
                'content' => Helper\Messaging::getMailIcs($process, $config)->getContent(),
                'base64' => false
            )
        ];
        return $entity;
    }
}
