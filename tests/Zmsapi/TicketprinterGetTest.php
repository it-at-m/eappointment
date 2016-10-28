<?php

namespace BO\Zmsapi\Tests;

class TicketprinterGetTest extends Base
{
    protected $classname = "TicketprinterGet";

    public function testRendering()
    {
        $response = $this->render(['ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2'], [], []);
        $this->assertContains('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotFound');
        $this->render(['12345678'], [], []);
    }
}
