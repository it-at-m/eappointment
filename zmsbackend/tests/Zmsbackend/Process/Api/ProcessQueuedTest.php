<?php

namespace BO\Zmsbackend\Tests\Process\Api;

class ProcessQueuedTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessQueued";

    public function testRendering()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation->getUseraccount()->setPermissions('appointment');
        $workstation['queue']['clusterEnabled'] = 1;

        $process = json_decode($this->readFixture("GetProcess_10030.json"));
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertStringContainsString('queued', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeHasRequests()
    {
        $this->expectException('BO\Zmsbackend\Matching\Exception\RequestNotFound');
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process->requests[0]->id = "9999999";
        $response = $this->render([], [
            '__body' => json_encode($process),
            'slotsRequired' => 2,
            'slotType' => 'intern'
        ], []);
    }

    public function testNoAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);

        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation->getUseraccount()->setPermissions('appointment');
        $workstation['queue']['clusterEnabled'] = 1;
        $workstation->process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process = json_decode($this->readFixture("GetProcess_10029.json"));
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "unvalid"
            }'
        ], []);
    }

    public function testProcessNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 0,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": 10029,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }
}
