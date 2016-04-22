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
}
