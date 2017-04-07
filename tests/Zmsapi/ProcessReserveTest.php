<?php

namespace BO\Zmsapi\Tests;

class ProcessReserveTest extends Base
{
    protected $classname = "ProcessReserve";

    public function testRendering()
    {
        $query = new \BO\Zmsdb\Process();

        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetFreeProcessList.json"))
        );
        $process = $processList->getFirstProcess();
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $responseData = json_decode($response->getBody(), 1);
        $processId = $responseData['data']['id'];
        $authKey = $responseData['data']['authKey'];

        $this->assertTrue('reserved' == $responseData['data']['status']);
        $this->assertTrue(200 == $response->getStatusCode());

        //delete tested data
        $query->deleteEntity($processId, $authKey);
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
        $this->assertEquals('2', $responseData['data']['appointments'][0]['slotCount']);
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
