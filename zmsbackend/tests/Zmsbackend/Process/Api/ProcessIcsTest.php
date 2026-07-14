<?php

namespace BO\Zmsbackend\Tests\Process\Api;

class ProcessIcsTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessIcs";

    public function testRendering()
    {
        $response = $this->render(['id' => 10030, 'authKey' => '1c56'], [], []);
        $this->assertStringContainsString('ics.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999999, 'authKey' => null], [], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('\BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => 10030, 'authKey' => null], [], []);
    }

    public function testFamilyNameIsNotAcceptedAsAuthKey()
    {
        $this->expectException('\BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => 10030, 'authKey' => 'Dayoff'], [], []);
    }
}
