<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Role as Query;
use BO\Zmsdb\Query\Base as BaseQuery;
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
}
