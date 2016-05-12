<?php

namespace BO\Zmsentities;

class Ics extends Schema\Entity
{
    public static $schema = "ics.json";

    public function getContent()
    {
        $content = $this->content;
        return ($this->isEncoding()) ? $content : \base64_decode($content);
    }

    public function isEncoding()
    {
        return (\base64_decode($this->content, true)) ? true : false;
    }
}
