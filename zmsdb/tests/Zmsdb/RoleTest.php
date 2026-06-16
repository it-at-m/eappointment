<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Role as Query;
use BO\Zmsentities\Role as Entity;

class RoleTest extends Base
{
    public function testReadRoleByIdExisting()
    {
        $query = new Query();
        $entity = $query->readRoleById(8);

        $this->assertEntity("\\BO\\Zmsentities\\Role", $entity);
        $this->assertEquals('system_admin', $entity->name);
    }

    public function testReadRoleByIdMissing()
    {
        $query = new Query();
        $missingEntity = $query->readRoleById(0);
        $this->assertNull($missingEntity);
    }


    public function testReadAllRoles()
    {
        $query = new Query();
        $collection = $query->readAllRoles();

        $this->assertEntityList("\\BO\\Zmsentities\\Role", $collection);
        $this->assertEquals(8, count($collection));
    }

    public function testAddRole()
    {
        $query = new Query();
        $input = new Entity([
            "name" => "test_role",
            "description" => "Test Role",
            "permissions" => ["superuser"],
        ]);
        $entity = $query->addRole($input);

        $this->assertEntity("\\BO\\Zmsentities\\Role", $entity);
        $this->assertNotNull($entity->id);
        $this->assertNotNull($entity->permissions);
        $this->assertContains("superuser", $entity->permissions);
    }

    public function testAddRoleWithUnknownPermissionThrows()
    {
        $query = new Query();
        $input = new Entity([
            "name" => "test_role_invalid_perm",
            "description" => "Test Role",
            "permissions" => ["__does_not_exist__"],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown permission name');

        $query->addRole($input);
    }

    public function testUpdateRoleMissingReturnsNull()
    {
        $query = new Query();
        $input = new Entity([
            "name" => "test_role_update_missing",
            "description" => "Test Role",
            "permissions" => [],
        ]);

        $updated = $query->updateRole(0, $input);
        $this->assertNull($updated);
    }

    public function testUpdateRoleExistingUpdatesFields()
    {
        $query = new Query();

        $created = $query->addRole(new Entity([
            "name" => "test_role_update_before",
            "description" => "Before",
            "permissions" => [],
        ]));

        $this->assertEntity("\\BO\\Zmsentities\\Role", $created);
        $roleId = (int) $created->id;

        $updated = $query->updateRole($roleId, new Entity([
            "name" => "test_role_update_after",
            "description" => "After",
            "permissions" => [],
        ]));

        $this->assertEntity("\\BO\\Zmsentities\\Role", $updated);
        $this->assertEquals($roleId, (int) $updated->id);
        $this->assertEquals("test_role_update_after", $updated->name);
        $this->assertEquals("After", $updated->description);
    }

    public function testUpdateRoleRewritesPermissionLinks()
    {
        $query = new Query();

        $created = $query->addRole(new Entity([
            'name' => 'test_role_perm_replace',
            'description' => 'Initial permissions',
            'permissions' => ['useraccount'],
        ]));

        $this->assertEntity('\\BO\\Zmsentities\\Role', $created);
        $roleId = (int) $created->id;
        $this->assertEquals(['useraccount'], $created->permissions);

        $updated = $query->updateRole($roleId, new Entity([
            'name' => 'test_role_perm_replace',
            'description' => 'Replaced permissions',
            'permissions' => ['superuser', 'logs'],
        ]));

        $this->assertEntity('\\BO\\Zmsentities\\Role', $updated);
        $this->assertEquals($roleId, (int) $updated->id);
        $this->assertEqualsCanonicalizing(['superuser', 'logs'], $updated->permissions);
    }

    public function testDeleteRoleRemovesRole()
    {
        $query = new Query();

        $created = $query->addRole(new Entity([
            "name" => "test_role_delete",
            "description" => "To delete",
            "permissions" => [],
        ]));

        $this->assertEntity("\\BO\\Zmsentities\\Role", $created);
        $roleId = (int) $created->id;

        $deleted = $query->deleteRole($roleId);
        $this->assertEntity("\\BO\\Zmsentities\\Role", $deleted);
        $this->assertEquals($roleId, (int) $deleted->id);

        $missing = $query->readRoleById($roleId);
        $this->assertNull($missing);
    }

    /**
     * Replace the database user_role relation for a user by loginname with the specified role id.
     *
     * This bypasses Useraccount::writeUpdatedEntity() intentionally because these tests use
     * dynamic test roles that are not part of the temporary LEGACY_LEVEL_BY_ROLE bridge.
     *
     * TODO ZMSKVR-1173: Remove this direct user_role setup once the legacy nutzer.Berechtigung
     * bridge is removed and role assignment no longer depends on legacy rights mapping.
     */
    private function replaceUserRoleDirectly(\BO\Zmsdb\Useraccount $userQuery, string $loginName, int $roleId): void
    {
        $userQuery->perform(
            'DELETE FROM user_role
             WHERE user_id = (SELECT NutzerID FROM nutzer WHERE Name = ? LIMIT 1)',
            [$loginName]
        );

        $userQuery->perform(
            'INSERT INTO user_role (user_id, role_id)
             SELECT NutzerID, ? FROM nutzer WHERE Name = ?',
            [$roleId, $loginName]
        );
    }

    public function testUpdateRoleInvalidatesCachedUserRoleNames()
    {
        $roleQuery = new Query();
        $userQuery = new \BO\Zmsdb\Useraccount();

        $createdRole = $roleQuery->addRole(new Entity([
            'name' => 'test_role_cache_before',
            'description' => 'Cached role name',
            'permissions' => ['useraccount'],
        ]));

        $user = (new \BO\Zmsentities\Useraccount())->getExample();
        $user->id = $user->id . rand();
        $createdUser = $userQuery->writeEntity($user);

        $this->replaceUserRoleDirectly(
            $userQuery,
            $createdUser->id,
            (int) $createdRole->id
        );
        $cached = $userQuery->readEntity($createdUser->id, 1, false);
        $this->assertSame(['test_role_cache_before'], $cached->roles);

        $roleQuery->updateRole((int) $createdRole->id, new Entity([
            'name' => 'test_role_cache_after',
            'description' => 'Cached role name',
            'permissions' => ['useraccount'],
        ]));
        $refreshed = $userQuery->readEntity($createdUser->id, 1, false);
        $this->assertSame(['test_role_cache_after'], $refreshed->roles);
    }

    public function testDeleteRoleRemovesAssignedUserRoleRelations()
    {
        $roleQuery = new Query();
        $userQuery = new \BO\Zmsdb\Useraccount();

        $createdRole = $roleQuery->addRole(new Entity([
            "name" => "test_role_delete_assigned",
            "description" => "Assigned role",
            "permissions" => ["useraccount"],
        ]));

        $this->assertEntity("\\BO\\Zmsentities\\Role", $createdRole);

        $user = (new \BO\Zmsentities\Useraccount())->getExample();
        $user->id = $user->id . rand();
        $createdUser = $userQuery->writeEntity($user);

        $this->replaceUserRoleDirectly(
            $userQuery,
            $createdUser->id,
            (int) $createdRole->id
        );

        $reloadedUser = $userQuery->readEntity($createdUser->id, 1, true);
        $this->assertIsArray($reloadedUser->roles);
        $this->assertContains($createdRole->name, $reloadedUser->roles);

        $deleted = $roleQuery->deleteRole((int) $createdRole->id);
        $this->assertEntity("\\BO\\Zmsentities\\Role", $deleted);

        $reloadedUserAfterDelete = $userQuery->readEntity($createdUser->id, 1, true);
        $this->assertIsArray($reloadedUserAfterDelete->roles);
        $this->assertNotContains($createdRole->name, $reloadedUserAfterDelete->roles);
    }
}
