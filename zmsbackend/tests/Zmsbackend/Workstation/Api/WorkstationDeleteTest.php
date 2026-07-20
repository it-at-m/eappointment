<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

use BO\Zmsbackend\Helper\User;

class WorkstationDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationDelete";

    public static $loginName = 'testadmin';



    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['loginname' => static::$loginName], [], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'test'], [], []);
    }

    public function testHasCalledProcess()
    {
        $workstation = $this->setWorkstation();
        $workstation->process = (new \BO\Zmsentities\Process)->getExample();
        $this->expectException('\BO\Zmsbackend\Workstation\Exception\WorkstationHasCalledProcess');
        $this->expectExceptionCode(428);
        $this->render(['loginname' => static::$loginName], [], []);
    }
}
