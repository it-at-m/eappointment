<?php

namespace BO\Zmsdb;

/**
 * Lightweight repository for role and role_permission operations.
 */
class Role extends Base
{
    /**
     * Read all roles and expand them into permission name lists.
     *
     * @return array<int, array{id:int,name:string,description:?string,permissions:array<int,string>}>
     */
    public function readRolePermissionMatrix(): array
    {
        $reader = $this->getReader();

        // Fetch all roles
        $sqlRoles = 'SELECT id, name, description FROM role ORDER BY name';
        $rows = $reader->fetchAll($sqlRoles, []);
        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        $rolesById = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $rolesById[$id] = [
                'id' => $id,
                'name' => $row['name'],
                'description' => $row['description'],
                'permissions' => [],
            ];
        }

        if (empty($rolesById)) {
            return [];
        }

        // Load permissions for all roles in one query
        $roleIds = array_keys($rolesById);
        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $sqlPermissions = '
            SELECT rp.role_id, p.name AS permission
            FROM role_permission rp
            INNER JOIN permission p ON p.id = rp.permission_id
            WHERE rp.role_id IN (' . $placeholders . ')
            ORDER BY p.name
        ';

        $permissionRows = $reader->fetchAll($sqlPermissions, $roleIds);
        if (is_array($permissionRows)) {
            foreach ($permissionRows as $row) {
                $roleId = (int) $row['role_id'];
                $permission = $row['permission'];
                if (isset($rolesById[$roleId])) {
                    $rolesById[$roleId]['permissions'][] = $permission;
                }
            }
        }

        // Normalize permission lists (unique, sorted)
        foreach ($rolesById as &$role) {
            $role['permissions'] = array_values(array_unique($role['permissions']));
            sort($role['permissions']);
        }
        unset($role);

        return array_values($rolesById);
    }

    /**
     * Apply create/update/delete actions for roles and their permissions.
     *
     * @param array<string,array> $rolesInput keyed by role ID
     * @param int[] $deleteIds list of role IDs to delete
     * @param array<string,mixed> $newRoleInput data for a new role
     */
    public function updateRoleAssignments(array $rolesInput, array $deleteIds, array $newRoleInput): void
    {
        $db = $this;

        // Normalize delete IDs to integers
        $deleteIds = array_values(array_unique(array_map('intval', $deleteIds)));

        // Existing roles: update or delete
        foreach ($rolesInput as $roleId => $roleData) {
            $roleId = (int) $roleId;
            if ($roleId <= 0) {
                continue;
            }

            // Delete role (and its permissions) if requested
            if (in_array($roleId, $deleteIds, true)) {
                $db->perform('DELETE FROM role_permission WHERE role_id = ?', [$roleId]);
                $db->perform('DELETE FROM role WHERE id = ?', [$roleId]);
                continue;
            }

            $name = isset($roleData['name']) ? trim((string) $roleData['name']) : '';
            $description = isset($roleData['description']) ? trim((string) $roleData['description']) : null;
            $permissionIds = isset($roleData['permissions']) && is_array($roleData['permissions'])
                ? array_values(array_unique(array_map('intval', $roleData['permissions'])))
                : [];

            if ($name === '') {
                continue;
            }

            // Update role meta data
            $db->perform(
                'UPDATE role SET name = ?, description = ? WHERE id = ?',
                [$name, $description, $roleId]
            );

            // Reset permissions for this role and insert current selection
            $db->perform('DELETE FROM role_permission WHERE role_id = ?', [$roleId]);
            foreach ($permissionIds as $permissionId) {
                if ($permissionId <= 0) {
                    continue;
                }
                $db->perform(
                    'INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (?, ?)',
                    [$roleId, $permissionId]
                );
            }
        }

        // New role creation
        $newName = isset($newRoleInput['name']) ? trim((string) $newRoleInput['name']) : '';
        if ($newName !== '') {
            $newDescription = isset($newRoleInput['description'])
                ? trim((string) $newRoleInput['description'])
                : null;
            $newPermissionIds = isset($newRoleInput['permissions']) && is_array($newRoleInput['permissions'])
                ? array_values(array_unique(array_map('intval', $newRoleInput['permissions'])))
                : [];

            // Insert new role
            $db->perform(
                'INSERT INTO role (name, description) VALUES (?, ?)',
                [$newName, $newDescription]
            );
            $writer = $db->getWriter();
            $roleId = (int) $writer->lastInsertId();

            if ($roleId > 0) {
                foreach ($newPermissionIds as $permissionId) {
                    if ($permissionId <= 0) {
                        continue;
                    }
                    $db->perform(
                        'INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (?, ?)',
                        [$roleId, $permissionId]
                    );
                }
            }
        }
    }
}
