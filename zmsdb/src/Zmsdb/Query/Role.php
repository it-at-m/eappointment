<?php

namespace BO\Zmsdb\Query;

class Role extends Base implements MappingInterface
{
    /**
     * @var string TABLE mysql table reference
     */
    public const TABLE = 'role';

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
            'assignedUserCount' => self::expression(
                '(SELECT COUNT(DISTINCT ur.user_id) FROM user_role ur WHERE ur.role_id = role.id)'
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
        $countKey = $this->getPrefixed('assignedUserCount');
        if (array_key_exists($countKey, $data)) {
            $data[$countKey] = (int) ($data[$countKey] ?? 0);
        }
        return $data;
    }

    public function addConditionName(string $name): self
    {
        $this->query->where('role.name', '=', $name);
        return $this;
    }

    public function addConditionNames(array $names): self
    {
        if ($names === []) {
            throw new \InvalidArgumentException('Argument $names must not be empty.');
        }
        $this->query->whereIn('role.name', $names);
        return $this;
    }

    public function addConditionRoleId(int $roleId): self
    {
        $this->query->where('role.id', '=', $roleId);
        return $this;
    }

    public function addOrderBy(string $parameter, string $order = 'ASC'): self
    {
        $this->query->orderBy('role.' . $parameter, $order);
        return $this;
    }
}
