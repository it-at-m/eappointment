<?php

namespace BO\Zmsentities;

class Mail extends Schema\Entity
{
    public static $schema = "mail.json";

    public function addMultiPart($multiPart)
    {
        $this->multipart = $multiPart;
        return $this;
    }

    public function isEncoding()
    {
        $multiPart = current($this->multipart);
        return $multiPart['base64'];
    }

    public function getHtmlPart()
    {
        foreach ($this->multipart as $part) {
            if ($part['mime'] == 'text/html') {
                $content = $part['content'];
                return ($this->isEncoding()) ? \base64_decode($content) : $content;
            }
        }
        return null;
    }

    public function getPlainPart()
    {
        foreach ($this->multipart as $part) {
            if ($part['mime'] == 'text/plain') {
                $content = $part['content'];
                return ($this->isEncoding()) ? \base64_decode($content) : $content;
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
}
