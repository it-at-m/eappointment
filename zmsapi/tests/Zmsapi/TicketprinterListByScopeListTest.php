<?php

namespace BO\Zmsapi\Tests;

class TicketprinterListByScopeListTest extends Base
{
    protected $classname = "TicketprinterListByScopeList";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['ticketprinter'] = true;
        $response = $this->render(['ids' => '141,169'], [], []);
        $this->assertStringContainsString('"buttonlist":"s141"', (string)$response->getBody());
        $this->assertStringContainsString('"buttonlist":"s169"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingTicketprinterPermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render(['ids' => '141,169'], [], []);
    }
}
