<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DepartmentDeleteTest extends Base
{
    protected $classname = "DepartmentDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department')
            ->addDepartment([
                'id' => 999
            ]);
        $response = $this->render(['id' => 999], [], []); //Test Department
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasChildren()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department')
            ->addDepartment([
                'id' => 74
            ]);
        $this->expectException('\BO\Zmsdb\Exception\Department\ScopeListNotEmpty');
        $this->expectExceptionCode(428);
        $this->render(['id' => 74], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        // The rights check does not know, if the department is missed because of rights or by lack of data
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['id' => 998], [], []);
    }

    public function testNoRights()
    {
        $this->setWorkstation()->getUseraccount()->addDepartment(['id' => 74]);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 999], [], []); //Test Department
    }
}
