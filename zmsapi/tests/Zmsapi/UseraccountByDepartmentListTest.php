<?php

namespace BO\Zmsapi\Tests;

class UseraccountByDepartmentListTest extends Base
{
    protected $classname = "UseraccountListByDepartments";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['ids' => 74], [], []);
        $this->assertStringContainsString('testuser', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testDepartmentNotAssigned()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['ids' => 72], [], []);
    }

    public function testDepartmentNotFound()
    {
        // Superusers skip department validation for performance, so non-existent departments
        // will just return an empty user list instead of throwing an exception
        $this->setWorkstation()->getUseraccount()->setRights('superuser', 'useraccount');
        $response = $this->render(['ids' => 99999], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
        // Should return empty list for non-existent department
        $this->assertStringNotContainsString('testuser', (string)$response->getBody());
    }
}
