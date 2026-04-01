<?php

namespace BO\Zmsdb\Query;

class UserRole extends Base implements MappingInterface
{
    /**
     * @var string TABLE mysql table reference
     */
    const TABLE = 'user_role';

    public function getEntityMapping()
    {
        return [
            'userId' => 'user_role.user_id',
            'roleId' => 'user_role.role_id',
        ];
    }

    public function addConditionUserId(int $userId): self
    {
        $this->query->where('user_role.user_id', '=', $userId);
        return $this;
    }

    public function addConditionUserIds(array $userIds): self
    {
        if ($userIds === []) {
            throw new \InvalidArgumentException('Argument $userIds must not be empty.');
        }
        $this->query->whereIn('user_role.user_id', $userIds);
        return $this;
    }
}
