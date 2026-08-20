<?php

namespace BO\Zmsentities;

class Permission extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = "permission.json";
}
