<?php

namespace BO\Zmsdb\Query;

class Role extends Base implements MappingInterface
{
    /**
     * @var string TABLE mysql table reference
     */
    const TABLE = 'role';

    public function getEntityMapping()
    {
        return [
            'id' => 'role.id',
            'name' => 'role.name',
            'description' => 'role.description',
            'permissions' => self::expression(
                '(SELECT GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR \',\') '
                . 'FROM role_permission rp '
                . 'JOIN permission p ON p.id = rp.permission_id '
                . 'WHERE rp.role_id = role.id)'
            ),
        ];
    }

    public function postProcess($data)
    {
        $permissionsKey = $this->getPrefixed('permissions');
        $rawPermissions = $data[$permissionsKey] ?? null;
        $data[$permissionsKey] = ($rawPermissions === null || $rawPermissions === '')
            ? []
            : explode(',', (string) $rawPermissions);
        return $data;
    }

    public function addConditionName(string $name): self
    {
        $this->query->where('role.name', '=', $name);
        return $this;
    }

    public function addConditionNames(array $names): self
    {
        if (!empty($names)) {
            $this->query->whereIn('role.name', $names);
        }
        return $this;
    }
}
