<?php

namespace BO\Zmsapi\Tests;

class ProcessGetTest extends Base
{
    protected $classname = "ProcessGet";

    public function testRendering()
    {
        $response = $this->render([10030,'1c56'], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testProcessNotFound()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $response = $this->render([123456,null], [], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $response = $this->render([10030,null], [], []);
    }
}
