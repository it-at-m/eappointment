<?php

namespace BO\Zmsdb\Query;

class RolePermission extends Base
{
    /**
     * @var string TABLE mysql table reference
     */
    const TABLE = 'role_permission';

    public function addConditionRoleId(int $roleId): self
    {
        $this->query->where('role_id', '=', $roleId);
        return $this;
    }
}
