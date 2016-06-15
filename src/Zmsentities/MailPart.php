<?php

namespace BO\Zmsentities;

class MailPart extends Schema\Entity
{
    public static $schema = "mailpart.json";

    public function isBase64Encoded()
    {
        return ($this->base64) ? true : false;
    }

    public function isHtml()
    {
        return ($this->mime == 'text/html') ? true : false;
    }

    public function isText()
    {
        return ($this->mime == 'text/plain') ? true : false;
    }

    public function isIcs()
    {
        return ($this->mime == 'text/calendar') ? true : false;
    }

    public function getContent()
    {
        return ($this->isBase64Encoded()) ? \base64_decode($this->content) : $this->content;
    }
}
