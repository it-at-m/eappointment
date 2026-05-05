<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Collection\RoleList as Collection;
use BO\Zmsentities\Permission as PermissionEntity;
use BO\Zmsentities\Role as Entity;

class Role extends Base
{
    public function readRoleById(int $roleId, int $resolveReferences = 1): ?Entity
    {
        if (null === $roleId) {
            return null;
        }

        $query = new Query\Role(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRoleId($roleId);

        return $this->fetchOne($query, new Entity());
    }

    public function readAllRoles(string $order = 'ASC', int $resolveReferences = 1): Collection
    {
        $roleList = new Collection();
        $query = new Query\Role(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addOrderBy('id', $order);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                $roleList->addEntity($entity);
            }
        }
        return $roleList;
    }

    public function addRole(Entity $role, int $resolveReferences = 1): ?Entity
    {
        $permissionNames = array_values(array_unique($role['permissions'] ?? []));
        $permissions = $this->fetchPermissionsByNamesOrFail($permissionNames);

        $query = new Query\Role(Query\Base::INSERT);
        $query->addValues(
            [
                'name' => $role['name'],
                'description' => $role['description'],
            ]
        );
        $this->writeItem($query);
        $newId = (int) $this->getWriter()->lastInsertId();

        foreach ($permissions as $permission) {
            $link = new Query\RolePermission(Query\Base::INSERT);
            $link->addValues(
                [
                    'role_id' => $newId,
                    'permission_id' => $permission['id'],
                ]
            );
            $this->writeItem($link);
        }

        return $this->readRoleById($newId, $resolveReferences);
    }

    public function updateRole(int $roleId, Entity $role, int $resolveReferences = 1): ?Entity
    {
        $existing = $this->readRoleById($roleId);
        if (!$existing || !$existing->hasId()) {
            return null;
        }

        $permissionNames = array_values(array_unique($role['permissions'] ?? []));
        $permissions = $this->fetchPermissionsByNamesOrFail($permissionNames);

        $query = new Query\Role(Query\Base::UPDATE);
        $query->addConditionRoleId($roleId);
        $query->addValues(
            [
                'name' => $role['name'],
                'description' => $role['description'],
            ]
        );
        $this->writeItem($query);

        $delLinks = new Query\RolePermission(Query\Base::DELETE);
        $delLinks->addConditionRoleId($roleId);
        $this->deleteItem($delLinks);

        foreach ($permissions as $permission) {
            $link = new Query\RolePermission(Query\Base::INSERT);
            $link->addValues(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permission['id'],
                ]
            );
            $this->writeItem($link);
        }

        return $this->readRoleById($roleId, $resolveReferences);
    }

    private function fetchPermissionsByNamesOrFail(array $permissionNames): array
    {
        if ($permissionNames === []) {
            return [];
        }
        $permQuery = new Query\Permission(Query\Base::SELECT);
        $permQuery->addEntityMapping()
            ->addResolvedReferences(0)
            ->addConditionNames($permissionNames);
        $permissions = $this->fetchList($permQuery, new PermissionEntity());
        if (count($permissions) !== count($permissionNames)) {
            $found = [];
            foreach ($permissions as $p) {
                $found[$p['name']] = true;
            }
            $missing = array_values(array_filter($permissionNames, static fn($n) => !isset($found[$n])));
            throw new \InvalidArgumentException(
                'Unknown permission name(s): ' . implode(', ', $missing)
            );
        }
        return $permissions;
    }

    public function deleteRole(int $roleId): ?Entity
    {
        $entity = $this->readRoleById($roleId);
        $query = new Query\Role(Query\Base::DELETE);
        $query->addConditionRoleId($roleId);
        return ($this->deleteItem($query)) ? $entity : null;
    }
}
