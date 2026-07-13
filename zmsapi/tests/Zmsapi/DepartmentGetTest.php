<?php

namespace BO\Zmsapi\Tests;

class DepartmentGetTest extends Base
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
        $this->expectException('\BO\Zmsapi\Exception\Department\DepartmentNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 99999], [], []);
    }
}
