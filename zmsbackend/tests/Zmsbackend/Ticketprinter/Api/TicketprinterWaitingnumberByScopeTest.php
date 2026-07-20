<?php

namespace BO\Zmsbackend\Tests\Ticketprinter\Api;

class TicketprinterWaitingnumberByScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "TicketprinterWaitingnumberByScope";

    public function testRendering()
    {
        //Schöneberg with test scope ghostWorkstationCount of 3
        $response = $this->render(['id' => 146, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"id":146', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeFailed()
    {
        $this->expectException('BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'hash' => '1abcdefghijklmnopqrstuvwxyz'], [], []);
    }
}
