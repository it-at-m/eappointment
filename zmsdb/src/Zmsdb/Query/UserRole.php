<?php

namespace BO\Zmsdb\Query;

class UserRole extends Base
{
    /**
     * @var string TABLE mysql table reference
     */
    const TABLE = 'user_role';

    public function addConditionUserId(int $userId): self
    {
        $this->query->where('user_id', '=', $userId);
        return $this;
    }

    public function addConditionRoleId(int $roleId): self
    {
        $this->query->where('role_id', '=', $roleId);
        return $this;
    }
}
