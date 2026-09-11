<?php

namespace BO\Zmsentities;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property array $permissions
 * @property int $assignedUserCount
 */
class Role extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = "role.json";

    #[\Override]
    public function getDefaults(): array
    {
        return [
            'permissions' => [],
            'assignedUserCount' => 0,
        ];
    }
}
