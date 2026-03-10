<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Query\Permission;
use BO\Zmsdb\Query\Role;
use BO\Zmsdb\Query\UserRole;

class PermissionQueryTest extends Base
{
    public function testPermissionGetEntityMapping()
    {
        $reflection = new \ReflectionClass(Permission::class);
        $this->assertSame('permission', $reflection->getConstant('TABLE'));
    }

    public function testRoleGetEntityMappingAndConditions()
    {
        $reflection = new \ReflectionClass(Role::class);
        $this->assertSame('role', $reflection->getConstant('TABLE'));

        $mapping = (new Role(\BO\Zmsdb\Query\Base::SELECT))->getEntityMapping();

        $this->assertArrayHasKey('id', $mapping);
        $this->assertArrayHasKey('name', $mapping);
        $this->assertArrayHasKey('description', $mapping);
    }

    public function testUserRoleGetEntityMappingAndConditions()
    {
        $reflection = new \ReflectionClass(UserRole::class);
        $this->assertSame('user_role', $reflection->getConstant('TABLE'));

        $mapping = (new UserRole(\BO\Zmsdb\Query\Base::SELECT))->getEntityMapping();

        $this->assertArrayHasKey('user_id', $mapping);
        $this->assertArrayHasKey('role_id', $mapping);
    }
}

