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
        // DELETE uses "DELETE <alias> FROM role_permission <alias>"; WHERE must use the alias, not role_permission.col
        $this->query->where(self::getAlias() . '.role_id', '=', $roleId);
        return $this;
    }
}
