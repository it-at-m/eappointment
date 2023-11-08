<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ScopeQueueTest extends Base
{
    protected $classname = "ScopeQueue";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('scope');
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('queue.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('queue.json', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testQueueEmpty()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('scope');
        $response = $this->render(['id' => 141], ['date' => '2015-04-01'], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('scope');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
