<?php

namespace BO\Zmsapi\Tests;

class TicketprinterWaitingnumberByClusterTest extends Base
{
    protected $classname = "TicketprinterWaitingnumberByCluster";

    public function testRendering()
    {
        //Schöneberg with test scope ghostWorkstationCount of 3
        /*
        $response = $this->render([4, 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2'], [], []);
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertContains('"id":"146"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        */
    }

    public function testClusterFailed()
    {
        $this->expectException('BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render([999, 'ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2'], [], []);
    }

    public function testUnvalidHash()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterHashNotValid');
        $this->expectExceptionCode(403);
        $response = $this->render([4, '123456687890'], [], []);
    }
}
