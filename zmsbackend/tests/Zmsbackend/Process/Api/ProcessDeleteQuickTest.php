<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use \BO\Zmsbackend\Process\Service\ProcessStatusFree;

class ProcessDeleteQuickTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessDeleteQuick";

    protected $processId;

    protected $authKey = '';

    public function testRendering()
    {
        $this->setWorkstation(123, 'testuser', 167)
            ->getUseraccount()
            ->setPermissions('appointment');
        $response = $this->render(['id' => 10029], [], []);
        $this->assertStringContainsString('blocked', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testIsCalled()
    {
        $this->setWorkstation(123, 'testuser', 141)
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('BO\Zmsbackend\Process\Exception\ProcessAlreadyCalled');
        $this->expectExceptionCode(404);
        $this->render(['id' => '9999999'], [], []);
    }

    public function testRenderingWithInitiator()
    {
        $this->setWorkstation(123, 'testuser', 451)
            ->getUseraccount()
            ->setPermissions('appointment');
        $response = $this->render(['id' => 27147], ['initiator' => 1], []);
        $this->assertStringContainsString('blocked', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthFailed()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('BO\Zmsbackend\Process\Exception\ProcessNoAccess');
        $this->expectExceptionCode(403);
        $this->render(['id' => '10030'], [], []);
    }

    public function testFailedDelete()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 0 ], [], []);
    }
}
