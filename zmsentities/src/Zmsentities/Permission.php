<?php

namespace BO\Zmsentities;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
class Permission extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = "permission.json";
}
