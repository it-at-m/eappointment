<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DepartmentDeleteTest extends Base
{
    protected $classname = "DepartmentDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $response = $this->render(['id' => 999], [], []); //Test Department
        $this->assertContains('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasChildren()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('\BO\Zmsdb\Exception\Department\ScopeListNotEmpty');
        $this->expectExceptionCode(428);
        $this->render(['id' => 74], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('\BO\Zmsapi\Exception\Department\DepartmentNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
