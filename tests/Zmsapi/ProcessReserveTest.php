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

        $this->assertContains('reserved', (string)$response->getBody());
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

        $this->assertContains('reserved', (string)$response->getBody());
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
}
