<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsdb\ProcessStatusFree;

class ProcessDeleteQuickTest extends Base
{
    protected $classname = "ProcessDeleteQuick";

    protected $processId;

    protected $authKey = '';

    public function testRendering()
    {
        $this->setWorkstation(123, 'testuser', 167);
        $response = $this->render(['id' => 10029], [], []);
        $this->assertStringContainsString('blocked', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testIsCalled()
    {
        $this->setWorkstation(123, 'testuser', 141);
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessAlreadyCalled');
        $this->expectExceptionCode(404);
        $this->render(['id' => '9999999'], [], []);
    }

    public function testRenderingWithInitiator()
    {
        $this->setWorkstation(123, 'testuser', 451);
        //$response = $this->render(['id' => 27147], ['initiator' => 1], []);
        //$this->assertStringContainsString('blocked', (string)$response->getBody());
        //$this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthFailed()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessNoAccess');
        $this->expectExceptionCode(403);
        $this->render(['id' => '10030'], [], []);
    }

    public function testFailedDelete()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 0 ], [], []);
    }
}
