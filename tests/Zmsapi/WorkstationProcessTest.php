<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WorkstationProcessTest extends Base
{
    protected $classname = "WorkstationProcess";

    const PROCESS_ID = 10029;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .'
            }'
        ], []);
        $this->assertContains(User::$workstation->process['id'], (string)$response->getBody());
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWorkstationWithProcess()
    {
        $this->setWorkstation();
        User::$workstation->process = (new \BO\Zmsentities\Process())->getExample();
        User::$workstation->process->id = 10029;
        User::$workstation->process->authKey = self::AUTHKEY;
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .'
            }'
        ], []);
        $this->assertContains("10029", (string)$response->getBody());
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testProcessAlreadyCalled()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessAlreadyCalled');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 9999999
            }'
        ], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [], []);
    }

    public function testProcessNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 123456
            }'
        ], []);
    }
}
