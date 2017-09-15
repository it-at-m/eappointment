<?php

namespace BO\Zmsapi\Tests;

class ProcessFinishedTest extends Base
{
    protected $classname = "ProcessFinished";

    public function testRendering()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;

        $process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process->status = 'finished';
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertContains('finished', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithSurveyAccepted()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;

        $process = json_decode($this->readFixture("GetProcess_10030.json"), 1);
        $process['status'] = 'finished';
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertContains('"surveyAccepted":1', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRenderingPending()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;

        $process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process->status = 'pending';
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertContains('pending', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidCredentials()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessInvalid');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => $this->readFixture("GetProcess_10030.json")
        ], []);
    }

    public function testNoAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);

        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $workstation->process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process = json_decode($this->readFixture("GetProcess_10029.json"));
        $process->status = 'finished';
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "confirmed"
            }'
        ], []);
    }

    public function testProcessNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "abcd",
                "status": "finished",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": 10029,
                "authKey": "abcd",
                "status": "finished",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }
}
