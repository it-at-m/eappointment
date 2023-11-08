<?php

namespace BO\Zmsentities;

class Mimepart extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "mimepart.json";

    public function getDefaults()
    {
        return [
            'mime' => '',
            'content' => '',
            ];
    }

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

    public function getExtension()
    {
        return preg_replace('#^.*/#', '', $this->mime);
    }
}
