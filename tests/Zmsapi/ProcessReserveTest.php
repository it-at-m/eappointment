<?php

namespace BO\Zmsapi\Tests;

class ProcessReserveTest extends Base
{
    protected $classname = "ProcessReserve";

    protected $processId;

    protected $authKey;

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
        $this->processId = $responseData['data']['id'];
        $this->authKey = $responseData['data']['authKey'];

        $this->assertTrue('reserved' == $responseData['data']['status']);
        $this->assertTrue(200 == $response->getStatusCode());

        //delete tested data
        $query->deleteEntity($this->processId, $this->authKey);
    }

    public function testMultipleSlots()
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
        $this->processId = $responseData['data']['id'];
        $this->authKey = $responseData['data']['authKey'];

        $this->assertTrue('reserved' == $responseData['data']['status']);
        $this->assertTrue(200 == $response->getStatusCode());

        //delete tested data
        $query->deleteEntity($this->processId, $this->authKey);
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
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessFailedReservation');
        $this->expectExceptionCode(404);
        $process = $query->readEntity(10030, '1c56'); //dayoff beispiel test
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }
}
