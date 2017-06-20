<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DepartmentByScopeIdTest extends Base
{
    protected $classname = "DepartmentByScopeId";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $response = $this->render(['id' => 141], [], []);
        $this->assertContains('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertContains('department.json', (string)$response->getBody());
        $this->assertContains('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('\BO\Zmsapi\Exception\Department\DepartmentNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
