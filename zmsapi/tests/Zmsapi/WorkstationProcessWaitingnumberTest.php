<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WorkstationProcessWaitingnumberTest extends Base
{
    protected $classname = "WorkstationProcessWaitingnumber";

    const PROCESS_ID = 10255;

    const AUTHKEY = '29ed';

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->queue['clusterEnabled'] = 1;
        $workstation->scope['id'] = 146; //ghostworkstation count 3
        $response = $this->render([], [
            '__body' => '{
                "scope": {"id": 146, "preferences":{"queue":{"processingTimeAverage":10}}}
            }'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"status":"queued"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        $process = new \BO\Zmsentities\Process(json_decode($response->getBody(), true)['data']);
        $entity = (new \BO\Zmsdb\Process)->readEntity($process->id, new \BO\Zmsdb\Helper\NoAuth);
        $this->assertEquals('queued', $entity->status);
    }

    public function testEmpty()
    {
        $workstation = $this->setWorkstation();
        $workstation->queue['clusterEnabled'] = 1;
        $workstation->scope['id'] = 146; //ghostworkstation count 3
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
