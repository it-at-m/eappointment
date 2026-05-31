<?php

namespace BO\Zmsentities;

class Permission extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static string $schema = "permission.json";
}
