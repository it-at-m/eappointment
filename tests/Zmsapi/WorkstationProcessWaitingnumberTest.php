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
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {"id": 146, "preferences":{"queue":{"processingTimeAverage":10}}}
            }'
        ], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertContains('"status":"queued"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
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
