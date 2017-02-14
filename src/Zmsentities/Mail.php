<?php

namespace BO\Zmsentities;

class Mail extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "mail.json";

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

    public function getFirstClient()
    {
        $client = null;
        if (count($this->process['clients']) > 0) {
            $data = current($this->process['clients']);
            $client = new Client($data);
        }
        return $client;
    }

    public function toResolvedEntity(Process $process, Config $config)
    {
        $entity = clone $this;
        $content = Helper\Messaging::getMailContent($process, $config);
        $entity->process = $process;
        $entity->subject = Helper\Messaging::getMailSubject($process, $config);
        $entity->createIP = $process->createIP;
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
