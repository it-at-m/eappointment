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

    public function isEncoding($string)
    {
        return (\base64_encode(\base64_decode($string, true)) === $string) ? true : false;
    }

    public function getHtmlPart()
    {
        foreach ($this->multipart as $part) {
            if ($part['mime'] == 'text/html') {
                $content = $part['content'];
                return ($this->isEncoding($content)) ? \base64_decode($content) : $content;
            }
        }
        return null;
    }

    public function getPlainPart()
    {
        foreach ($this->multipart as $part) {
            if ($part['mime'] == 'text/plain') {
                $content = $part['content'];
                return ($this->isEncoding($content)) ? \base64_decode($content) : $content;
            }
        }
        return null;
    }

    public function getIcsPart()
    {
        foreach ($this->multipart as $part) {
            if ($part['mime'] == 'text/calendar') {
                $content = $part['content'];
                return ($this->isEncoding($content)) ? \base64_decode($content) : $content;
            }
        }
        return null;
    }

    public function toPlainText($content)
    {
        $replaceThis = array(
            '<br />' => '\n',
            '<li>' => '\n- ',
            '</li>' => '',
            '<h2>' => '\n',
            '</h2>' => '\n',
            );

        $content = \preg_replace('!\s+!m', ' ', $content);
        $content = \str_replace(array_keys($replaceThis), $replaceThis, $content);
        $content = \strip_tags($content);

        $lines = \explode("\n", $content);
        $new_lines = array();
        foreach ($lines as $line) {
            if (!empty($line)) {
                $new_lines[]=$line;
            }
        }
        $content = \implode("\n", $new_lines);
        $content = \html_entity_decode($content);
        return $content;
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
        $content = Helper\Messaging::createMessage($process, $config);
        $entity->process = $process;
        $entity->subject = Helper\Messaging::createSubject($process, $config);
        $entity->createIP = $process->createIP;
        $entity->department = $process->getDepartment();
        $entity->multipart = [
            array(
                'mime' => 'text/html',
                'content' => $content
            ),
            array(
                'mime' => 'text/plain',
                'content' => $this->toPlainText($content)
            ),
            array(
                'mime' => 'text/calendar',
                'content' => Helper\Messaging::createIcs($process, $config)->getContent()
            )
        ];
        return $entity;
    }
}
