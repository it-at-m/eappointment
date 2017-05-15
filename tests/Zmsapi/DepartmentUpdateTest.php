<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DepartmentUpdateTest extends Base
{
    protected $classname = "DepartmentUpdate";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('department');
        $response = $this->render(["id"=> 999], [
            '__body' => '{
                  "id": 999,
                  "name": "Test Department Update"
              }'
        ], []);
        $this->assertContains('Test Department Update', (string)$response->getBody());
        $this->assertContains('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('department');
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('department');
        $this->expectException('\BO\Zmsapi\Exception\Department\DepartmentNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id"=> 1], [
            '__body' => '{
                  "id": 9999
              }'
        ], []);
    }
}
