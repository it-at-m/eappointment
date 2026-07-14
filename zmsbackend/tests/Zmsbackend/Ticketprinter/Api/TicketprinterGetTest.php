<?php

namespace BO\Zmsbackend\Tests\Ticketprinter\Api;

class TicketprinterGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "TicketprinterGet";

    public function testRendering()
    {
        $response = $this->render(['hash' => '71abcdefghijklmnopqrstuvwxyz'], [], []);
        $this->assertStringContainsString('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Ticketprinter\Exception\TicketprinterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['hash' => '12345678'], [], []);
    }

    public function testEmpty()
    {
        $this->expectException('\Exception');
        $this->render([], [], []);
    }
}