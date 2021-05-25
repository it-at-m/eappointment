<?php

namespace BO\Zmsapi\Tests;

class DepartmentUseraccountListTest extends Base
{
    protected $classname = "DepartmentUseraccountList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['id' => 74], [], []);
        $this->assertStringContainsString('testuser', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testDepartmentNotAssigned()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [], []);
    }

    public function testDepartmentNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser', 'useraccount');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['id' => 99999], [], []);
    }
}
