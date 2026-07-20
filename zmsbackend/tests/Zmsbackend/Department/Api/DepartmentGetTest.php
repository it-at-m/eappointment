<?php

namespace BO\Zmsbackend\Tests\Department\Api;

use BO\Zmsbackend\Helper\User;

class DepartmentGetTest extends \BO\Zmsbackend\Tests\Api\Base

{
    protected $classname = "DepartmentGet";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 74], [], []);
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setPermissions('department');
        $this->expectException('\BO\Zmsbackend\Department\Exception\DepartmentNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 99999], [], []);
    }
}
