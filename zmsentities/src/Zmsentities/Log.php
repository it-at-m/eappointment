<?php

namespace BO\Zmsentities;

class Log extends Schema\Entity
{
    public const PRIMARY = 'reference';

    public static $schema = "log.json";
}
