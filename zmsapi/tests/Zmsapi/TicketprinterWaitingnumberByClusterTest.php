<?php

namespace BO\Zmsapi\Tests;

class TicketprinterWaitingnumberByClusterTest extends Base
{
    protected $classname = "TicketprinterWaitingnumberByCluster";

    public function testRendering()
    {
        //Schöneberg with test scope ghostWorkstationCount of 3
        $response = $this->render(['id' => 4, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"id":"146"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterFailed()
    {
        $this->expectException('BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
    }

    public function testProcessReserveFailed()
    {
        $this->expectException('BO\Zmsdb\Exception\Cluster\ScopesWithoutWorkstationCount');
        $this->expectExceptionCode(404);
        $this->render(['id' => 76, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
    }
}
