<?php

namespace BO\Zmsbackend\Role\Repository;

class RolePermission extends \BO\Zmsbackend\Query\Base
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
