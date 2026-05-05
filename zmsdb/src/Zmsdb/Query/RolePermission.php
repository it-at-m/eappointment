<?php

namespace BO\Zmsdb\Query;

/**
 * Junction table role ↔ permission (writes only).
 */
class RolePermission extends Base
{
    public const TABLE = 'role_permission';

    public function addConditionRoleId(int $roleId): self
    {
        $this->query->where('role_id', '=', $roleId);
        return $this;
    }
}
