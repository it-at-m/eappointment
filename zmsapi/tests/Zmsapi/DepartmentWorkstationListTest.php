<?php

namespace BO\Zmsapi\Tests;

class DepartmentWorkstationListTest extends Base
{
    protected $classname = "DepartmentWorkstationList";

    public function testRendering()
    {
        $this->setWorkstation()->useraccount->setPermissions('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['id' => 74], [], []);
        $this->assertStringContainsString('testuser', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testDepartmentNotAssigned()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [], []);
    }

    public function testDepartmentNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser', 'useraccount');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['id' => 99999], [], []);
    }

    public function testMissingUseraccountPermission()
    {
        $this->setWorkstation();
        $this->setDepartment(74);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 74], [], []);
    }
}
