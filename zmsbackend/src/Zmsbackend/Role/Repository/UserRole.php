<?php

namespace BO\Zmsbackend\Role\Repository;

class UserRole extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var string TABLE mysql table reference
     */
    const string TABLE = 'user_role';

    /** @psalm-api */
    public function addConditionUserId(int $userId): self
    {
        $this->query->where('user_id', '=', $userId);
        return $this;
    }

    /** @psalm-api */
    public function addConditionRoleId(int $roleId): self
    {
        $this->query->where('role_id', '=', $roleId);
        return $this;
    }
}
