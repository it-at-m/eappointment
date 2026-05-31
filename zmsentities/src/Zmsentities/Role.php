<?php

namespace BO\Zmsentities;

class Role extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static ?string $schema = "role.json";

    public function getDefaults(): array
    {
        return [
            'permissions' => [],
            'assignedUserCount' => 0,
        ];
    }
}
