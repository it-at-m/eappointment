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
        $process = $processList->getFirst();
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertStringContainsString('"status":"confirmed"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        $entity = (new \BO\Zmsdb\Process)->readEntity($process->id, new \BO\Zmsdb\Helper\NoAuth);
        $this->assertEquals('confirmed', $entity->status);
    }

    public function testUnvalidInput()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "confirmed"
            }'
        ], []);
    }

    // TODO: fix this test when testing openid
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

    public function testProcessNotFound()
    {
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
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->render([], [
            '__body' => '{
                "id": 10029,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }
}
