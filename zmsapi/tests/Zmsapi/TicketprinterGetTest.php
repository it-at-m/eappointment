<?php

namespace BO\Zmsapi\Tests;

class TicketprinterGetTest extends Base
{
    protected $classname = "TicketprinterGet";

    public function testRendering()
    {
        $response = $this->render(['hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
        $this->assertStringContainsString('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['hash' => '12345678'], [], []);
    }

    public function testEmpty()
    {
        $this->expectException('\Exception');
        $this->render([], [], []);
    }
}
