<?php

namespace BO\Zmsentities;

class Role extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static $schema = "role.json";

    public function getDefaults()
    {
        return [
            'permissions' => [],
        ];
    }
}
