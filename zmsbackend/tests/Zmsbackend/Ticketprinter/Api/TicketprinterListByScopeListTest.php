<?php

namespace BO\Zmsbackend\Tests\Ticketprinter\Api;

class TicketprinterListByScopeListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "TicketprinterListByScopeList";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['ids' => '141,169'], [], []);
        $this->assertStringContainsString('"buttonlist":"s141"', (string)$response->getBody());
        $this->assertStringContainsString('"buttonlist":"s169"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
