<?php

namespace BO\Zmsentities;

class Ics extends Schema\Entity
{
    public const string PRIMARY = 'content';

    public static $schema = "ics.json";

    public function getContent()
    {
        return $this->content;
    }
}
