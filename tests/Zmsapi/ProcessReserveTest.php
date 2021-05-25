<?php

namespace BO\Zmsapi\Tests;

class ProcessReserveTest extends Base
{
    protected $classname = "ProcessReserve";

    public function testRendering()
    {
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirst();
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertStringContainsString('"status":"reserved"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithSlotsRequired()
    {
        $this->setWorkstation();
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirst();
        $response = $this->render([], [
            '__body' => json_encode($process),
            'slotsRequired' => 1,
            'slotType' => 'intern'
        ], []);

        $this->assertStringContainsString('reserved', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithSlotsRequiredExceeded()
    {
        $this->expectException('BO\Zmsdb\Exception\Process\ProcessReserveFailed');
        $this->setWorkstation();
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirst();
        $response = $this->render([], [
            '__body' => json_encode($process),
            'slotsRequired' => 2,
            'slotType' => 'intern'
        ], []);
    }

    public function testScopeHasRequests()
    {
        $this->expectException('BO\Zmsapi\Exception\Matching\RequestNotFound');
        $this->setWorkstation();
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getLast();
        $response = $this->render([], [
            '__body' => json_encode($process),
            'slotsRequired' => 2,
            'slotType' => 'intern'
        ], []);
    }

    public function testMultipleSlots()
    {
        $process = new \BO\Zmsentities\Process(
            json_decode($this->readFixture("GetProcessWithMultipleSlotCount.json"), 1)
        );
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $responseData = json_decode($response->getBody(), 1);
        $this->assertEquals('3', $responseData['data']['appointments'][0]['slotCount']);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testInvalidInput()
    {
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testReservationFailed()
    {
        $query = new \BO\Zmsdb\Process();
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessReserveFailed');
        $this->expectExceptionCode(404);
        $process = $query->readEntity(10030, '1c56'); //dayoff beispiel test
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }

    public function testWithClientkey()
    {
        $this->setWorkstation();
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirst();
        $process->appointments[0]->slotCount = 3;
        $response = $this->render([], [
            '__body' => json_encode($process),
           'clientkey' => 'default'
        ], []);

        $this->assertStringContainsString('"slotCount":"1"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithClientkeyBlocked()
    {
        $query = new \BO\Zmsdb\Process();
        $this->expectException('BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $this->setWorkstation();
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirst();
        $this->render([], [
            '__body' => json_encode($process),
            'clientkey' => '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE'
        ], []);
    }

    public function testWithClientkeyInvalid()
    {
        $query = new \BO\Zmsdb\Process();
        $this->expectException('BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $this->setWorkstation();
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirst();
        $this->render([], [
            '__body' => json_encode($process),
            'clientkey' => '__invalid'
        ], []);
    }
}
