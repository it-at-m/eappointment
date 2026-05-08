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
}
