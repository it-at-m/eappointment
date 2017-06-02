<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WorkstationDeleteTest extends Base
{
    protected $classname = "WorkstationDelete";

    public static $loginName = 'berlinonline';

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['loginname' => static::$loginName], [], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'test'], [], []);
    }
}
