<?php

namespace BO\Zmsentities;

class Ics extends Schema\Entity
{
    public static $schema = "ics.json";

    public function getContent()
    {
        return $this->content;
    }
}
