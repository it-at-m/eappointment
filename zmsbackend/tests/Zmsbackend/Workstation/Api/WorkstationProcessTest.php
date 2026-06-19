<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

use \BO\Zmsbackend\Helper\User;

use \BO\Zmsentities\Process as Entity;

class WorkstationProcessTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationProcess";

    const PROCESS_ID = 11468;

    const AUTHKEY = '7b41';

    const SCOPE_ID = 143;

    public function tearDown(): void
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        parent::tearDown();
    }

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => json_encode($this->getInput())
        ], []);
        $this->assertStringContainsString(User::$workstation->process['id'], (string)$response->getBody());
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity(User::$workstation->process['id'], new \BO\Zmsbackend\Helper\NoAuth);
        $this->assertEquals('called', $entity->status);
    }

    public function testClusterWideCallDisabled()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => json_encode($this->getInput()),
            'allowClusterWideCall' => false
        ], []);
        $this->assertStringContainsString(User::$workstation->process['id'], (string)$response->getBody());
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWorkstationWithProcessAssigned()
    {
        $this->setWorkstation();
        User::$workstation->process = $this->getInput();
        $this->expectException('\BO\Zmsbackend\Workstation\Exception\WorkstationHasAssignedProcess');
        $response = $this->render([], [
            '__body' => '{
                "id": 10029
            }'
        ], []);
    }

    public function testProcessAlreadyCalled()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessAlreadyCalled');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "status": "called"
            }',
            'allowClusterWideCall' => true
        ], []);
    }

    public function testProcessReserved()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessReservedNotCallable');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "status": "reserved"
            }',
            'allowClusterWideCall' => true
        ], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [], []);
    }

    public function testCallFutureAppointmentBlocked()
    {
        \App::$now = new \DateTimeImmutable('2016-05-15 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $this->setWorkstation();
        
        $input = $this->getInput();
        $input->id = 10030; // Process with appointment on 2016-05-16
        $input->authKey = '1c56';
        
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotCurrentDate');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => json_encode($input)
        ], []);
    }

    public function testCallPastAppointmentBlocked()
    {
        \App::$now = new \DateTimeImmutable('2016-05-17 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $this->setWorkstation();
        
        $input = $this->getInput();
        $input->id = 10030; // Process with appointment on 2016-05-16
        $input->authKey = '1c56';
        
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotCurrentDate');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => json_encode($input)
        ], []);
    }

    protected function getInput()
    {
        $input = (new Entity)->createExample();
        $input->id = self::PROCESS_ID;
        $input->authKey = self::AUTHKEY;
        $input->scope['id'] = self::SCOPE_ID;
        $input->appointments[0]->scope['id'] = self::SCOPE_ID;
        return $input;
    }
}
