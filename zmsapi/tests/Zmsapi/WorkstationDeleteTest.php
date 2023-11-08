<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WorkstationDeleteTest extends Base
{
    protected $classname = "WorkstationDelete";

    public static $loginName = 'testadmin';

    public function __construct()
    {
        parent::__construct();
    }

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
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'test'], [], []);
    }

    public function testHasCalledProcess()
    {
        $workstation = $this->setWorkstation();
        $workstation->process = (new \BO\Zmsentities\Process)->getExample();
        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationHasCalledProcess');
        $this->expectExceptionCode(428);
        $this->render(['loginname' => static::$loginName], [], []);
    }
}
