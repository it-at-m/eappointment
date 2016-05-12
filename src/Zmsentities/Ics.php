<?php

namespace BO\Zmsentities;

class Ics extends Schema\Entity
{
    public static $schema = "ics.json";

    public function getContent()
    {
        return $this->content;
    }

    public function isEncoding()
    {
        return (\base64_encode(\base64_decode($this->content, true)) === $this->content) ? true : false;
    }
}
