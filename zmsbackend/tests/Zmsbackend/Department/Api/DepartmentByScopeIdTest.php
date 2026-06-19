<?php

namespace BO\Zmsbackend\Tests\Department\Api;

use BO\Zmsbackend\Helper\User;

class DepartmentByScopeIdTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "DepartmentByScopeId";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('\BO\Zmsbackend\Department\Exception\DepartmentNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
