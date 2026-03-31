<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WorkstationProcessParkedTest extends Base
{
    protected $classname = "WorkstationProcessParked";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->name = '24';
        User::$workstation->process = (new \BO\Zmsentities\Process())->getExample();
        User::$workstation->process->id = self::PROCESS_ID;
        User::$workstation->process->authKey = self::AUTHKEY;
        
        $response = $this->render([], [], []);
        
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"process"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        // Verify the process was set to parked status
        $entity = (new \BO\Zmsdb\Process)->readEntity(self::PROCESS_ID, new \BO\Zmsdb\Helper\NoAuth);
        $this->assertEquals('parked', $entity->status);
        $this->assertEquals('24', $entity->parkedBy);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [], []);
    }
}

