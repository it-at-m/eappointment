<?php

namespace BO\Zmsbackend\Tests\Ticketprinter\Api;

class TicketprinterWaitingnumberByClusterTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "TicketprinterWaitingnumberByCluster";

    public function testRendering()
    {
        //Schöneberg with test scope ghostWorkstationCount of 3
        $response = $this->render(['id' => 4, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"id":146', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterFailed()
    {
        $this->expectException('BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
    }

    public function testProcessReserveFailed()
    {
        $this->expectException('BO\Zmsbackend\Cluster\Exception\ScopesWithoutWorkstationCount');
        $this->expectExceptionCode(404);
        $this->render(['id' => 76, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
    }
}
