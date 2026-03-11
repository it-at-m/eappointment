<?php

namespace BO\Zmsdb;

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
     * Return a map of role-id => role-name for the given ids.
     *
     * @param int[] $ids
     * @return array<int,string>
     */
    public function readRoleNamesByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = sprintf('SELECT id, name FROM role WHERE id IN (%s)', $placeholders);

        $rows = $this->getReader()->fetchAll($sql, $ids);
        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (!isset($row['id'], $row['name'])) {
                continue;
            }
            $result[(int) $row['id']] = (string) $row['name'];
        }

        return $result;
    }

    /**
     * Apply create/update actions for roles and their permissions.
     *
     * @param array<string,array> $rolesInput keyed by role ID
     * @param array<string,mixed> $newRoleInput data for a new role
     */
    public function updateRoleAssignments(array $rolesInput, array $newRoleInput): void
    {
        $db = $this;

        // Existing roles: update
        foreach ($rolesInput as $roleId => $roleData) {
            $roleId = (int) $roleId;
            if ($roleId <= 0) {
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

        // Note: deletion of roles is handled by a dedicated path (see deleteRoleById()).

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

    /**
     * Delete a role and all of its permission assignments.
     *
     * @param int $roleId
     */
    public function deleteRoleById(int $roleId): void
    {
        $roleId = (int) $roleId;
        if ($roleId <= 0) {
            return;
        }

        $this->perform('DELETE FROM role_permission WHERE role_id = ?', [$roleId]);
        $this->perform('DELETE FROM role WHERE id = ?', [$roleId]);
    }
}
