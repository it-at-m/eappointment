<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ProcessPickupTest extends Base
{
    protected $classname = "ProcessPickup";

    const PROCESS_ID = 10030;
    const AUTHKEY = '1c56';
    const SCOPE_ID = 141;

    public function testRendering()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": '. self::SCOPE_ID . '
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "email": "max@service.berlin.de",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": '. self::SCOPE_ID . '
                        },
                        "slotCount": 2
                    }
                ],
                "status": "pickup"
            }'
        ], []);
        $this->assertStringContainsString('pickup', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);

        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $workstation->process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process = json_decode($this->readFixture("GetProcess_10029.json"));
        $process->scope = (new \BO\Zmsentities\Scope())->getExample();
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }

    public function testWorkstationHasAssignedProcess()
    {
        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationHasAssignedProcess');
        $this->expectExceptionCode(404);

        $workstation = $this->setWorkstation(138, 'berlinonline', 167);
        $workstation['queue']['clusterEnabled'] = 1;
        $workstation->process = new \BO\Zmsentities\Process(
            json_decode(
                $this->readFixture("GetProcess_10030.json"),
                1
            )
        );
        $process = json_decode($this->readFixture("GetProcess_10029.json"));
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }

    public function testUnvalidInput()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "pickup"
            }'
        ], []);
    }

    public function testQueue()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $response = $this->render([], [
            '__body' => '{
                "queue": {
                    "number": "55"
                }
            }'
        ], []);
        $this->assertStringContainsString('pickup', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testProcessNotFound()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->render([], [
            '__body' => '{
                "id": 10029,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testInvalidProcess()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessInvalid');
        $this->expectExceptionCode(400);
        $response = $this->render([], [
            '__body' => '{
                "id": 10029
            }'
        ], []);
    }
}
