<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsapi\Helper\User;

use \BO\Zmsentities\Process as Entity;

class WorkstationProcessTest extends Base
{
    protected $classname = "WorkstationProcess";

    const PROCESS_ID = 11468;

    const AUTHKEY = '7b41';

    const SCOPE_ID = 143;

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => json_encode($this->getInput())
        ], []);
        $this->assertStringContainsString(User::$workstation->process['id'], (string)$response->getBody());
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        $entity = (new \BO\Zmsdb\Process)->readEntity(User::$workstation->process['id'], new \BO\Zmsdb\Helper\NoAuth);
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
        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationHasAssignedProcess');
        $response = $this->render([], [
            '__body' => '{
                "id": 10029
            }'
        ], []);
    }

    public function testWorkstationWithPickupAssigned()
    {
        $this->setWorkstation();
        User::$workstation->process = $this->getInput();
        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationHasAssignedProcess');
        $response = $this->render([], [
            '__body' => '{
                "id": 10029,
                "status": "pickup"
            }'
        ], []);
    }

    /*public function testProcessAlreadyCalled()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessAlreadyCalled');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "status": "called"
            }',
            'allowClusterWideCall' => true
        ], []);
    }*/

    /*public function testProcessReserved()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessReservedNotCallable');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "status": "reserved"
            }',
            'allowClusterWideCall' => true
        ], []);
    }*/

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [], []);
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
