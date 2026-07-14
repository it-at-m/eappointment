<?php

namespace BO\Zmsbackend\Tests\Scope\Api;

use BO\Zmsbackend\Helper\User;

class ScopeQueueTest extends \BO\Zmsbackend\Tests\Api\Base

{
    protected $classname = "ScopeQueue";

    public function testRendering()
    {
        $this->setWorkstation();
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
        $response = $this->render(['id' => 141], ['date' => '2015-04-01'], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setPermissions('scope');
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
