<?php

namespace BO\Zmsapi\Tests;

class TicketprinterWaitingnumberByClusterTest extends Base
{
    protected $classname = "TicketprinterWaitingnumberByCluster";

    public function testRendering()
    {
        //Schöneberg with test scope ghostWorkstationCount of 3
        $response = $this->render(['id' => 4, 'hash' => 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2'], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertContains('"id":"146"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterFailed()
    {
        $this->expectException('BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'hash' => 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2'], [], []);
    }

    public function testProcessReserveFailed()
    {
        $this->expectException('BO\Zmsdb\Exception\Cluster\ScopesWithoutWorkstationCount');
        $this->expectExceptionCode(404);
        $this->render(['id' => 76, 'hash' => 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2'], [], []);
    }

    public function testUnvalidHash()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterHashNotValid');
        $this->expectExceptionCode(403);
        $this->render(['id' => 4, 'hash' => '123456687890'], [], []);
    }

    public function testDisabledTicketprinter()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotEnabled');
        $this->expectExceptionCode(200);
        $this->render(['id' => 4, 'hash' => 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f3'], [], []);
    }
}
