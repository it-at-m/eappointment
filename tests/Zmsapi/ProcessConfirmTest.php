<?php

namespace BO\Zmsapi\Tests;

class ProcessConfirmTest extends Base
{
    protected $classname = "ProcessConfirm";

    public function testRendering()
    {
        $processList = new \BO\Zmsentities\Collection\ProcessList(
            json_decode($this->readFixture("GetReservedProcessList.json"))
        );
        $process = $processList->getFirstProcess();
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertContains('confirmed', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testInvalidInput()
    {
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testNotReservedStatus()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessNotReservedAnymore');
        $this->expectExceptionCode(404);
        $process = json_decode($this->readFixture("GetProcess_10029.json"));
        $process->status = 'free';
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }
}
