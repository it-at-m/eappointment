<?php

namespace BO\Zmsapi\Tests;

class ProcessGetTest extends Base
{
    protected $classname = "ProcessGet";

    public function testRendering()
    {
        $response = $this->render([10030,'1c56'], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
        //by name
        $response = $this->render([10030,'Dayoff'], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testProcessNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render([123456,null], [], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $response = $this->render([10030,null], [], []);
    }
}
